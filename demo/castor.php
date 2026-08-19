<?php

use Castor\Attribute\AsTask;
use function Castor\context;
use function Castor\run;
use function Castor\capture;
use function Castor\io;
use function Castor\fs;
use function Castor\open;

// Context helpers pour éviter la répétition
function api_context(): Castor\Context
{
    return context()->withWorkingDirectory(__DIR__.'/api');
}

function client_symfony_context(): Castor\Context
{
    return context()->withWorkingDirectory(__DIR__.'/client-symfony');
}

function client_spa_context(): Castor\Context
{
    return context()->withWorkingDirectory(__DIR__.'/client-spa');
}

function compose_context(): Castor\Context
{
    return context()->withWorkingDirectory(__DIR__);
}

#[AsTask(name: 'install', description: 'Installe les dépendances de l\'API, du client Symfony et du client SPA')]
function install(): void
{
    // composer install dans api/
    io()->text('Installation des dépendances de l\'API...');
    run('composer install', context: api_context());

    // composer install dans client-symfony/
    io()->text('Installation des dépendances du client Symfony...');
    run('composer install', context: client_symfony_context());

    // bun install dans client-spa/
    io()->text('Installation des dépendances du client SPA...');
    run('bun install', context: client_spa_context());

    io()->success('Toutes les dépendances sont installées.');
}

#[AsTask(name: 'start', description: 'Démarre toute la démo : Keycloak, API, client Symfony et client SPA')]
function start(): void
{
    // 1. docker compose up -d --wait
    io()->text('Démarrage des conteneurs Docker...');
    run('docker compose up -d --wait', context: compose_context());

    // 2. reset de la base de données API (appel direct à la fonction db:reset)
    io()->text('Réinitialisation de la base de données API...');
    db_reset();

    // 3. purge du pool cache.app de l'API
    io()->text('Purge du cache JWKS de l\'API...');
    run('php bin/console cache:pool:clear cache.app', context: api_context());

    // 4. symfony server:start pour l'API sur le port 8100
    io()->text('Démarrage du serveur de l\'API sur le port 8100...');
    run('symfony server:start -d --port=8100 --no-tls', context: api_context()->withAllowFailure());

    // 5. symfony server:start pour le client Symfony sur le port 8101
    io()->text('Démarrage du serveur du client Symfony sur le port 8101...');
    run('symfony server:start -d --port=8101 --no-tls', context: client_symfony_context()->withAllowFailure());

    // 6. Vite dev server pour le client SPA, en arrière-plan
    io()->text('Démarrage du serveur Vite du client SPA sur le port 5173...');
    fs()->mkdir(__DIR__.'/var');
    // La sortie est redirigée vers un fichier : sinon le processus détaché garde le tuyau
    // ouvert et run() attend indéfiniment. Le PID est celui du shell de bun.
    if ('000' === trim(capture('curl -s -o /dev/null --max-time 2 -w "%{http_code}" http://localhost:5173/', onFailure: '000'))) {
        run('nohup bun run dev > ../var/spa.log 2>&1 & echo $! > ../var/spa.pid', context: client_spa_context());
        sleep(3);
    } else {
        io()->text('Vite écoute déjà sur 5173, on le laisse tranquille.');
    }

    // 7. Ne jamais annoncer le succès sans l'avoir vérifié : les server:start tolèrent
    //    l'échec (relance sur une stack déjà lancée), donc leur code de retour ne prouve rien.
    $services = [
        'Keycloak' => 'http://localhost:8080/realms/photos/.well-known/openid-configuration',
        'API' => 'http://localhost:8100/api/docs',
        'Client Symfony' => 'http://localhost:8101/',
        'Client SPA' => 'http://localhost:5173/',
    ];
    $morts = [];
    foreach ($services as $nom => $url) {
        $code = trim(capture('curl -s -o /dev/null --max-time 5 -w "%{http_code}" '.escapeshellarg($url), onFailure: '000'));
        if (!str_starts_with($code, '2')) {
            $morts[] = sprintf('%s (%s a répondu %s)', $nom, $url, $code);
        }
    }

    if ($morts) {
        io()->error("Ces services ne répondent pas :\n  - ".implode("\n  - ", $morts));
        io()->note('Regardez demo/var/spa.log pour le SPA, et "castor logs:keycloak" pour Keycloak.');

        throw new RuntimeException('La démo n\'est pas complètement démarrée.');
    }

    // 8. Vérifier que l'horloge du conteneur colle à celle de l'hôte : le token handler
    //    de Symfony ne tolère AUCUNE dérive (allowedTimeDrift: 0 sur iat, nbf et exp).
    //    Après une veille du portable, la VM Docker peut décaler et tout tomber en 401.
    verifier_horloge();

    io()->success('Toute la démo est démarrée !');
    
    io()->table(
        ['Acteur', 'Rôle', 'URL'],
        [
            ['CloudPics ID', 'OIDC Provider (admin / admin)', 'http://localhost:8080'],
            ['CloudPics API', 'Resource server', 'http://localhost:8100/api/docs'],
            ['PhotoPrint', 'Client public, PKCE', 'http://localhost:5173/'],
            ['PhotoBook', 'Client confidentiel', 'http://localhost:8101/'],
        ]
    );
    io()->note([
        'alice / alice : compte complet, PHOTOS_READ + PHOTOS_WRITE. Elle lit et dépose.',
        'bob / bob : offre gratuite, PHOTOS_READ seul. Il lit, mais son POST tombe en 403.',
    ]);
}

/**
 * Le token handler oidc de Symfony vérifie iat, nbf et exp avec allowedTimeDrift: 0,
 * une valeur codée en dur dans le composant. Une seconde de décalage entre l'horloge de
 * l'hôte et celle du conteneur Keycloak suffit donc à faire rejeter tous les tokens.
 */
function verifier_horloge(): void
{
    $conteneur = trim(capture('docker compose exec -T keycloak date +%s', context: compose_context()->withAllowFailure(), onFailure: ''));
    if ('' === $conteneur || !ctype_digit($conteneur)) {
        return;
    }

    $derive = abs((int) $conteneur - time());
    if ($derive > 2) {
        io()->warning(sprintf(
            "L'horloge du conteneur Keycloak est décalée de %d s par rapport à l'hôte.\n".
            "Le token handler de Symfony ne tolère aucune dérive : tous les tokens seront rejetés.\n".
            'Relancez Docker Desktop, puis "castor restart".',
            $derive
        ));
    }
}

#[AsTask(name: 'stop', description: 'Arrête toute la démo : SPA, serveurs Symfony et conteneurs Docker')]
function stop(): void
{
    // Kill PID du SPA s'il existe
    $pidFile = __DIR__.'/var/spa.pid';
    if (file_exists($pidFile)) {
        $pid = (int) trim(file_get_contents($pidFile));
        if ($pid > 0 && posix_kill($pid, 0)) {
            io()->text("Arrêt du serveur Vite (PID: $pid)...");
            run("kill $pid", context: context()->withAllowFailure());
            // Attendre un peu pour la fin du processus
            sleep(1);
            if (posix_kill($pid, 0)) {
                run("kill -9 $pid", context: context()->withAllowFailure());
            }
            unlink($pidFile);
        } else {
            // PID invalide ou processus déjà mort
            unlink($pidFile);
        }
    }

    // symfony server:stop pour l'API
    io()->text('Arrêt du serveur de l\'API...');
    run('symfony server:stop', context: api_context()->withAllowFailure());

    // symfony server:stop pour le client Symfony
    io()->text('Arrêt du serveur du client Symfony...');
    run('symfony server:stop', context: client_symfony_context()->withAllowFailure());

    // docker compose down -v
    io()->text('Arrêt des conteneurs Docker...');
    run('docker compose down -v', context: compose_context()->withAllowFailure());

    io()->success('Toute la démo est arrêtée.');
}

#[AsTask(name: 'restart', description: 'Redémarre toute la démo (stop puis start)')]
function restart(): void
{
    stop();
    start();
}

#[AsTask(name: 'open', description: 'Ouvre les quatre URLs dans le navigateur')]
function open_urls(): void
{
    open('http://localhost:5173/');        // SPA
    open('http://localhost:8101/');        // Symfony client
    open('http://localhost:8100/api/docs'); // API docs
    open('http://localhost:8080/');        // Keycloak admin
    io()->success('Les quatre URLs sont ouvertes dans le navigateur.');
}

#[AsTask(name: 'reset', namespace: 'db', description: 'Réinitialise la base de données de l\'API et insère deux photos de démo')]
function db_reset(): void
{
    $dbPath = __DIR__.'/api/var/data.db';
    
    // Supprimer la base de données existante
    if (file_exists($dbPath)) {
        io()->text('Suppression de la base de données existante...');
        unlink($dbPath);
    }

    // Créer le schéma
    io()->text('Création du schéma de la base de données...');
    run('php bin/console doctrine:schema:create', context: api_context());

    // Insérer deux photos de démo
    // Le Photo entity a: id (auto), title (string), url (string)
    io()->text('Insertion des photos de démo...');
    run('php bin/console dbal:run-sql "INSERT INTO photo (title, url) VALUES (\'Coucher de soleil sur le Golden Gate\', \'https://cloudpics.example/alice/golden-gate.jpg\')"', context: api_context());
    run('php bin/console dbal:run-sql "INSERT INTO photo (title, url) VALUES (\'Alice au sommet du Mont Tamalpais\', \'https://cloudpics.example/alice/tamalpais.jpg\')"', context: api_context());

    io()->success('Base de données réinitialisée avec deux photos de démo.');
}

#[AsTask(name: 'smoke', description: 'Teste l\'API en ligne de commande sans navigateur')]
function smoke(): void
{
    // cloudpics-smoke-test existe uniquement pour le test CLI. Le flow password est déprécié
    // par OAuth 2.1 : il n'est jamais montré dans le talk.
    $clientId = 'cloudpics-smoke-test';
    $realm = 'photos';
    $keycloakUrl = 'http://localhost:8080';
    $apiUrl = 'http://localhost:8100';

    $allPassed = true;

    verifier_horloge();

    // Pré-vol : sans Keycloak ni API, tous les tests échouent pour la même raison.
    foreach (['Keycloak' => $keycloakUrl.'/realms/'.$realm.'/.well-known/openid-configuration', 'API' => $apiUrl.'/api/docs'] as $name => $url) {
        if ('000' === trim(capture('curl -s -o /dev/null --max-time 5 -w "%{http_code}" '.escapeshellarg($url), onFailure: '000'))) {
            io()->error(sprintf('%s ne répond pas sur %s. Lancez "castor start".', $name, $url));
            exit(1);
        }
    }

    // Fonction helper pour obtenir un token
    $getToken = function ($username, $password) use ($clientId, $realm, $keycloakUrl) {
        $cmd = sprintf(
            'curl -s -X POST "%s/realms/%s/protocol/openid-connect/token" \
             -H "Content-Type: application/x-www-form-urlencoded" \
             -d "client_id=%s&grant_type=password&username=%s&password=%s"',
            $keycloakUrl,
            $realm,
            $clientId,
            $username,
            $password
        );
        return json_decode(capture($cmd), true);
    };

    // Fonction helper pour tester une URL.
    // Un POST doit porter un corps JSON-LD, sinon l'API répond 400/415 et jamais 201/403.
    $testUrl = function ($url, $token = null, $method = 'GET') {
        $cmd = sprintf('curl -s -o /dev/null -w "%%{http_code}" -X %s %s', $method, escapeshellarg($url));
        if ('POST' === $method) {
            $cmd .= " -H 'Content-Type: application/ld+json'"
                .' --data '.escapeshellarg(json_encode(['title' => 'Photo smoke', 'url' => 'https://example.com/smoke.jpg']));
        }
        if (null !== $token) {
            $cmd .= ' -H '.escapeshellarg('Authorization: Bearer '.$token);
        }

        // onFailure : curl sort en erreur si rien n'écoute. On veut un FAIL lisible,
        // pas une stack trace de castor au milieu d'une démo.
        return trim(capture($cmd.' --max-time 5', onFailure: '000'));
    };

    // 1. no token -> 401
    $response = $testUrl($apiUrl . '/api/photos', null, 'GET');
    $passed = $response === '401';
    io()->text(sprintf('[%s] no token -> 401: %s', $passed ? 'PASS' : 'FAIL', $response));
    if (!$passed) $allPassed = false;

    // 2. Bearer not.a.jwt -> 401
    $response = $testUrl($apiUrl . '/api/photos', 'not.a.jwt', 'GET');
    $passed = $response === '401';
    io()->text(sprintf('[%s] Bearer not.a.jwt -> 401: %s', $passed ? 'PASS' : 'FAIL', $response));
    if (!$passed) $allPassed = false;

    // Obtenir les tokens
    $aliceToken = $getToken('alice', 'alice');
    $bobToken = $getToken('bob', 'bob');

    if (!isset($aliceToken['access_token'], $bobToken['access_token'])) {
        io()->error('Keycloak n\'a pas délivré de token. Le realm "photos" est-il bien importé ?');
        exit(1);
    }

    // 3. alice GET /api/photos -> 200
    $response = $testUrl($apiUrl . '/api/photos', $aliceToken['access_token'], 'GET');
    $passed = $response === '200';
    io()->text(sprintf('[%s] alice GET /api/photos -> 200: %s', $passed ? 'PASS' : 'FAIL', $response));
    if (!$passed) $allPassed = false;

    // 4. alice POST /api/photos -> 201
    $response = $testUrl($apiUrl . '/api/photos', $aliceToken['access_token'], 'POST');
    $passed = $response === '201';
    io()->text(sprintf('[%s] alice POST /api/photos -> 201: %s', $passed ? 'PASS' : 'FAIL', $response));
    if (!$passed) $allPassed = false;

    // 5. bob GET /api/photos -> 200
    $response = $testUrl($apiUrl . '/api/photos', $bobToken['access_token'], 'GET');
    $passed = $response === '200';
    io()->text(sprintf('[%s] bob GET /api/photos -> 200: %s', $passed ? 'PASS' : 'FAIL', $response));
    if (!$passed) $allPassed = false;

    // 6. bob POST /api/photos -> 403
    $response = $testUrl($apiUrl . '/api/photos', $bobToken['access_token'], 'POST');
    $passed = $response === '403';
    io()->text(sprintf('[%s] bob POST /api/photos -> 403: %s', $passed ? 'PASS' : 'FAIL', $response));
    if (!$passed) $allPassed = false;

    if ($allPassed) {
        io()->success('Tous les tests smoke sont passés !');
    } else {
        io()->error('Certains tests smoke ont échoué !');
        exit(1);
    }
}

#[AsTask(name: 'keycloak', namespace: 'logs', description: 'Affiche les logs de Keycloak en continu')]
function logs_keycloak(): void
{
    run('docker compose logs -f keycloak', context: compose_context());
}

#[AsTask(name: 'cc', description: 'Vide le cache des deux applications PHP (cache:clear + cache:pool:clear cache.app)')]
function cc(): void
{
    io()->text('Vider le cache de l\'API...');
    run('php bin/console cache:clear', context: api_context());
    run('php bin/console cache:pool:clear cache.app', context: api_context());

    io()->text('Vider le cache du client Symfony...');
    run('php bin/console cache:clear', context: client_symfony_context());
    run('php bin/console cache:pool:clear cache.app', context: client_symfony_context());

    io()->success('Cache vidé pour les deux applications.');
}
