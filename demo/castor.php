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

#[AsTask(name: 'certs', description: 'Génère le certificat TLS de CloudPics ID avec mkcert')]
function certs(): void
{
    // Symfony 8.2 exige HTTPS pour le token_endpoint, même sur localhost.
    // PHP ne lit pas le trousseau macOS : on copie rootCA.pem pour le passer via cafile.
    $certDir = __DIR__.'/keycloak/certs';
    $certFile = $certDir.'/localhost.pem';
    $keyFile = $certDir.'/localhost-key.pem';
    $rootCAFile = $certDir.'/rootCA.pem';

    if (file_exists($certFile) && file_exists($keyFile) && file_exists($rootCAFile)) {
        io()->text('Le certificat mkcert pour localhost existe déjà.');
        return;
    }

    // Vérifier que mkcert est installé
    $mkcertPath = trim(capture('command -v mkcert', onFailure: ''));
    if ('' === $mkcertPath) {
        io()->error('mkcert est introuvable. Installez-le avec : brew install mkcert && mkcert -install');
        throw new RuntimeException('mkcert est requis pour générer le certificat.');
    }

    io()->text('Génération du certificat mkcert pour localhost...');
    fs()->mkdir($certDir);

    // Générer le certificat
    run('mkcert -cert-file '.escapeshellarg($certFile).' -key-file '.escapeshellarg($keyFile).' localhost 127.0.0.1 ::1');

    // Copier rootCA.pem
    $caRoot = trim(capture('mkcert -CAROOT'));
    fs()->copy($caRoot.'/rootCA.pem', $rootCAFile);

    // Mettre la clé en 0644 (Keycloak tourne avec l'utilisateur 1000)
    fs()->chmod($keyFile, 0644);

    io()->success('Certificat mkcert généré dans keycloak/certs/.');
}

#[AsTask(name: 'install', description: 'Installe les dépendances de l\'API, du client Symfony et du client SPA')]
function install(): void
{
    certs();

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
    certs();

    // 1. docker compose up -d --wait
    io()->text('Démarrage des conteneurs Docker...');
    run('docker compose up -d --wait', context: compose_context());

    // 2. reset de la base de données API (appel direct à la fonction db:reset)
    io()->text('Réinitialisation de la base de données API...');
    db_reset();

    // 3. purge du pool cache.app des deux apps PHP
    io()->text('Purge du cache JWKS des deux apps PHP...');
    run('php bin/console cache:pool:clear cache.app', context: api_context());
    // PhotoBook y garde le JWKS qui vérifie la signature de l'ID token, et Keycloak
    // régénère ses clés à chaque démarrage.
    run('php bin/console cache:pool:clear cache.app', context: client_symfony_context());

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
    $rootCAPath = __DIR__.'/keycloak/certs/rootCA.pem';
    $services = [
        'Keycloak' => 'https://localhost:8443/realms/photos/.well-known/openid-configuration',
        'API' => 'http://localhost:8100/api/docs',
        'Client Symfony' => 'http://localhost:8101/',
        'Client SPA' => 'http://localhost:5173/',
    ];
    $morts = [];
    foreach ($services as $nom => $url) {
        $cmd = 'curl -s -o /dev/null --max-time 5 -w "%{http_code}" '.escapeshellarg($url);
        if (str_starts_with($url, 'https://')) {
            $cmd .= ' --cacert '.escapeshellarg($rootCAPath);
        }
        $code = trim(capture($cmd, onFailure: '000'));
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
            ['CloudPics ID', 'OIDC Provider (admin / admin)', 'https://localhost:8443'],
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
    open('https://localhost:8443/');        // Keycloak admin
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

    // Insérer les photos de démo avec leur propriétaire
    // Le Photo entity a: id (auto), title (string), url (string), owner (string)
    io()->text('Insertion des photos de démo...');
    run('php bin/console dbal:run-sql "INSERT INTO photo (title, url, owner) VALUES (\'Coucher de soleil sur le Golden Gate\', \'https://cloudpics.example/alice/golden-gate.jpg\', \'11111111-1111-4111-8111-111111111111\')"', context: api_context());
    run('php bin/console dbal:run-sql "INSERT INTO photo (title, url, owner) VALUES (\'Alice au sommet du Mont Tamalpais\', \'https://cloudpics.example/alice/tamalpais.jpg\', \'11111111-1111-4111-8111-111111111111\')"', context: api_context());
    run('php bin/console dbal:run-sql "INSERT INTO photo (title, url, owner) VALUES (\'Bob en lecture seule\', \'https://cloudpics.example/bob/read-only.jpg\', \'22222222-2222-4222-8222-222222222222\')"', context: api_context());

    io()->success('Base de données réinitialisée avec trois photos de démo.');
}

#[AsTask(name: 'smoke', description: 'Teste l\'API en ligne de commande sans navigateur')]
function smoke(): void
{
    // cloudpics-smoke-test existe uniquement pour le test CLI. Le flow password est déprécié
    // par OAuth 2.1 : il n'est jamais montré dans le talk.
    $clientId = 'cloudpics-smoke-test';
    $realm = 'photos';
    $keycloakUrl = 'https://localhost:8443';
    $apiUrl = 'http://localhost:8100';
    $rootCAPath = __DIR__.'/keycloak/certs/rootCA.pem';

    $allPassed = true;

    verifier_horloge();

    // Pré-vol : sans Keycloak ni API, tous les tests échouent pour la même raison.
    foreach (['Keycloak' => $keycloakUrl.'/realms/'.$realm.'/.well-known/openid-configuration', 'API' => $apiUrl.'/api/docs'] as $name => $url) {
        $cmd = 'curl -s -o /dev/null --max-time 5 -w "%{http_code}" '.escapeshellarg($url);
        if (str_starts_with($url, 'https://')) {
            $cmd .= ' --cacert '.escapeshellarg($rootCAPath);
        }
        if ('000' === trim(capture($cmd, onFailure: '000'))) {
            io()->error(sprintf('%s ne répond pas sur %s. Lancez "castor start".', $name, $url));
            exit(1);
        }
    }

    // Fonction helper pour obtenir un token.
    //
    // Le smoke demande photos:read et photos:write, exactement comme PhotoPrint et
    // PhotoBook : sans ces scopes, CloudPics ID n'inscrit aucun rôle dans l'access
    // token et tout répondrait 403. Ce qui distingue alice de bob n'est pas ce que
    // le client demande, c'est ce que le Provider accorde.
    $getToken = function ($username, $password) use ($clientId, $realm, $keycloakUrl, $rootCAPath) {
        $cmd = sprintf(
            'curl -s -X POST "%s/realms/%s/protocol/openid-connect/token" \
             -H "Content-Type: application/x-www-form-urlencoded" \
             -d "client_id=%s&grant_type=password&username=%s&password=%s&scope=openid+photos%%3Aread+photos%%3Awrite" \
             --cacert %s',
            $keycloakUrl,
            $realm,
            $clientId,
            $username,
            $password,
            escapeshellarg($rootCAPath)
        );
        return json_decode(capture($cmd), true);
    };

    // La commande curl commune aux deux helpers ci-dessous.
    // Un POST doit porter un corps JSON-LD, sinon l'API répond 400/415 et jamais 201/403.
    $buildCurl = function ($url, $token = null, $method = 'GET') {
        $cmd = sprintf('curl -s -X %s %s', $method, escapeshellarg($url));
        if ('POST' === $method) {
            $cmd .= " -H 'Content-Type: application/ld+json'"
                .' --data '.escapeshellarg(json_encode(['title' => 'Photo smoke', 'url' => 'https://example.com/smoke.jpg']));
        }
        if (null !== $token) {
            $cmd .= ' -H '.escapeshellarg('Authorization: Bearer '.$token);
        }

        return $cmd;
    };

    // Fonction helper pour tester une URL et récupérer le code HTTP.
    $testUrl = function ($url, $token = null, $method = 'GET', $returnBody = false) use ($buildCurl) {
        $cmd = $buildCurl($url, $token, $method);

        // onFailure : curl sort en erreur si rien n'écoute. On veut un FAIL lisible,
        // pas une stack trace de castor au milieu d'une démo.
        if ($returnBody) {
            return capture($cmd.' --max-time 5', onFailure: '000');
        }
        return trim(capture($cmd.' -o /dev/null -w "%{http_code}" --max-time 5', onFailure: '000'));
    };

    // Le corps ET le code HTTP, en un seul appel. Indispensable dès que la requête
    // n'est pas rejouable : le POST du test 4 crée une photo, le relancer pour lire
    // son statut en créerait une deuxième.
    $testUrlFull = function ($url, $token = null, $method = 'GET') use ($buildCurl) {
        $raw = capture($buildCurl($url, $token, $method).' -w "\n%{http_code}" --max-time 5', onFailure: "\n000");
        $cut = strrpos($raw, "\n");

        return [
            'status' => false === $cut ? '000' : trim(substr($raw, $cut + 1)),
            'body' => false === $cut ? '' : substr($raw, 0, $cut),
        ];
    };

    // Fonction helper pour tester une URL et compter les éléments de la collection.
    //
    // API Platform 4 sérialise en JSON-LD 1.1 : les clefs sont « totalItems » et « member »,
    // sans le préfixe « hydra: » des versions précédentes. Les deux formes sont acceptées ici,
    // pour que le test survive à une bascule de configuration.
    $testCollectionCount = function ($url, $token) use ($testUrl) {
        $body = $testUrl($url, $token, 'GET', true);
        $data = json_decode($body, true);
        if (!is_array($data)) {
            return -1;
        }

        return $data['totalItems']
            ?? $data['hydra:totalItems']
            ?? count($data['member'] ?? $data['hydra:member'] ?? []);
    };

    // Les propriétaires des photos rendues par la collection.
    //
    // C'est l'invariant qui compte, et il est stable : un compte figé casserait au deuxième
    // passage, puisque le POST d'alice laisse une photo de plus derrière lui.
    $collectionOwners = function ($url, $token) use ($testUrl) {
        $data = json_decode($testUrl($url, $token, 'GET', true), true);
        if (!is_array($data)) {
            return null;
        }

        return array_map(
            static fn (array $photo) => $photo['owner'] ?? null,
            $data['member'] ?? $data['hydra:member'] ?? []
        );
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

    // Obtenir les IDs des photos pour les tests suivants
    // Alice a 2 photos, Bob en a 1. On récupère les IDs via une requête admin (pas de filtre)
    // Mais on ne peut pas faire ça en smoke test... On va deviner les IDs : 1, 2 pour Alice, 3 pour Bob
    // Les sub sont déterministes : les id des utilisateurs sont épinglés dans le realm.
    $aliceSub = '11111111-1111-4111-8111-111111111111';
    $bobSub = '22222222-2222-4222-8222-222222222222';
    $alicePhotoIds = [1, 2];
    $bobPhotoId = 3;

    // 3. alice GET /api/photos : elle voit au moins ses deux photos, et QUE les siennes
    $owners = $collectionOwners($apiUrl . '/api/photos', $aliceToken['access_token']);
    $passed = is_array($owners) && count($owners) >= 2 && [$aliceSub] === array_values(array_unique($owners));
    io()->text(sprintf('[%s] alice GET /api/photos -> que ses photos (cloisonnement): %d photos, %d propriétaire(s)',
        $passed ? 'PASS' : 'FAIL', is_array($owners) ? count($owners) : -1, is_array($owners) ? count(array_unique($owners)) : -1));
    if (!$passed) $allPassed = false;

    // 4. alice POST /api/photos -> 201, et le serveur impose le propriétaire
    ['status' => $status, 'body' => $body] = $testUrlFull($apiUrl . '/api/photos', $aliceToken['access_token'], 'POST');
    $created = json_decode($body, true);
    $passed = '201' === $status && is_array($created) && ($created['owner'] ?? null) === $aliceSub;
    io()->text(sprintf('[%s] alice POST /api/photos -> 201 et owner imposé par le serveur: HTTP %s, owner %s',
        $passed ? 'PASS' : 'FAIL', $status, $created['owner'] ?? '(aucun owner dans la réponse)'));
    if (!$passed) $allPassed = false;

    // 5. bob GET /api/photos : la sienne, et rien d'autre
    $owners = $collectionOwners($apiUrl . '/api/photos', $bobToken['access_token']);
    $passed = is_array($owners) && 1 === count($owners) && [$bobSub] === array_values(array_unique($owners));
    io()->text(sprintf('[%s] bob GET /api/photos -> que la sienne (cloisonnement): %d photo(s)',
        $passed ? 'PASS' : 'FAIL', is_array($owners) ? count($owners) : -1));
    if (!$passed) $allPassed = false;

    // 6. bob GET /api/photos/{id d'une photo d'Alice} -> 403 (cloisonnement démontré)
    $response = $testUrl($apiUrl . '/api/photos/' . $alicePhotoIds[0], $bobToken['access_token'], 'GET');
    $passed = $response === '403';
    io()->text(sprintf('[%s] bob GET /api/photos/{photo d\'Alice} -> 403 (cloisonnement): %s', $passed ? 'PASS' : 'FAIL', $response));
    if (!$passed) $allPassed = false;

    // 7. alice GET /api/photos/{id de sa propre photo} -> 200
    $response = $testUrl($apiUrl . '/api/photos/' . $alicePhotoIds[0], $aliceToken['access_token'], 'GET');
    $passed = $response === '200';
    io()->text(sprintf('[%s] alice GET /api/photos/{sa photo} -> 200: %s', $passed ? 'PASS' : 'FAIL', $response));
    if (!$passed) $allPassed = false;

    // 8. bob POST /api/photos -> 403
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

#[AsTask(name: 'test', description: 'Lance les tests unitaires et fonctionnels de l\'API et de PhotoBook')]
function test(): void
{
    // Contrairement à « castor smoke », ces tests ne demandent ni Keycloak, ni Docker,
    // ni serveur : le Provider et l'API sont simulés au niveau du transport HTTP.
    io()->text('Tests de CloudPics API...');
    run('php bin/phpunit', context: api_context());

    io()->text('Tests de PhotoBook...');
    run('php bin/phpunit', context: client_symfony_context());

    io()->success('Les deux suites de tests sont passées.');
}
