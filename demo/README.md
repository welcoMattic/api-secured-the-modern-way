# Démo : déléguer l'authentification à un OIDC Provider

Démo compagnon du talk **API Secured, the Modern Way** (API Platform Con 2026).

Elle montre **une seule idée** : l'API n'authentifie plus personne. Un OIDC Provider émet les tokens,
l'API se contente de les **vérifier** avec les token handlers **natifs** de Symfony.

La partie « API Platform comme serveur d'autorisation OAuth2 » (`league/oauth2-server-bundle`)
est volontairement hors périmètre.

## La démo reprend l'histoire du deck

Le talk raconte **Alice**, **PhotoPrint** et **CloudPics**. La démo distribue les mêmes rôles, et en
ajoute deux que la bascule vers OIDC rend nécessaires.

| Acteur | Rôle | Dossier | Techno | URL |
|---|---|---|---|---|
| ☁️ **CloudPics API** | Resource server : les photos d'Alice vivent ici | `api/` | API Platform 4 / Symfony 8.1 | http://localhost:8100 |
| 🛂 **CloudPics ID** | OIDC Provider : comptes, login, émission des tokens | `keycloak/` | Keycloak 26 (Docker) | http://localhost:8080 |
| 🌁 **PhotoPrint** | Client **public** : tourne chez Alice, aucun secret à garder | `client-spa/` | Vite + TypeScript + `oidc-client-ts` | http://localhost:5173 |
| 📕 **PhotoBook** | Client **confidentiel** : tourne sur son serveur, lui peut garder un secret | `client-symfony/` | Symfony 8.1 + `drenso/symfony-oidc-bundle` | http://localhost:8101 |

Dans la section OAuth2 du deck, **CloudPics** cumule deux rôles : serveur d'autorisation *et* resource
server. Toute la démonstration OIDC consiste à lui retirer le premier. CloudPics garde les photos,
**CloudPics ID** prend l'identité. C'est pour ça que la démo compte quatre acteurs là où l'histoire
d'origine en comptait trois.

**PhotoPrint** garde le rôle que la slide « Rien ne prouve que c'est PhotoPrint » lui donne : une app
qui tourne chez Alice, donc incapable de garder un secret, donc PKCE. **PhotoBook** est son pendant
confidentiel : un autre service tiers qui veut les photos d'Alice, mais qui tourne sur son propre
serveur. Les deux passent par le même Provider, et l'API ne fait aucune différence entre eux.

```
  🌁 PhotoPrint  ─┐                             ┌─ authorization_code + PKCE ─→ 🛂 CloudPics ID
                  ├─ Bearer access_token ─→ ☁️ CloudPics API ─ discovery ────→ 🛂 CloudPics ID
  📕 PhotoBook  ──┘                             └─ vérification offline (signature RS256)
```

## Les deux chemins d'authentification

| | 🌁 PhotoPrint | 📕 PhotoBook |
|---|---|---|
| **Où tourne le client ?** | Chez Alice, dans son navigateur | Sur le serveur de PhotoBook |
| **Qui initie le flow ?** | Le navigateur, via `oidc-client-ts` | Le serveur, via `drenso/symfony-oidc-bundle` |
| **Client OIDC** | Public + PKCE S256 | Confidentiel (`client_secret`) + PKCE |
| **Ce que Symfony fournit nativement** | Rien côté client : c'est du JS | Pas encore le flow `authorization_code` ([PR #64954](https://github.com/symfony/symfony/pull/64954)) |
| **Côté API** | `access_token` + token handler `oidc` (natif) | Identique : le même firewall, le même handler |

Le point clé : **côté CloudPics API, rien ne change**. Le resource server ne sait pas quel type de
client lui parle, et il n'a pas à le savoir.

### Le beat à ne pas rater sur scène

Connectez-vous d'abord sur PhotoPrint, puis ouvrez PhotoBook et cliquez sur « Se connecter ».
**Aucun écran de login n'apparaît** : la session est déjà ouverte chez CloudPics ID. Les deux apps
affichent alors le **même `sub`**, et chacune a reçu ses propres tokens.

Une authentification, un Provider, deux clients tiers. Aucune des deux apps n'a jamais vu le mot de
passe d'Alice, et aucune ne sait que l'autre existe.

> **Pourquoi 8100 / 8101 et pas 8000 / 8001 ?** Pour qu'une démo live n'entre jamais en collision
> avec un autre serveur Symfony déjà lancé sur la machine. Les ports sont regroupés dans `castor.php`
> et dans les fichiers `.env` de chaque app.

## Prérequis

- PHP >= 8.4, [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download)
- [Castor](https://castor.jolicode.com/)
- Docker + Docker Compose
- [Bun](https://bun.sh/)

## Démarrage

```bash
castor install   # composer install x2 + bun install
castor start     # CloudPics ID + CloudPics API + PhotoBook + PhotoPrint
castor open      # ouvre les 3 apps et la console de CloudPics ID
```

`castor stop` arrête tout. `castor smoke` vérifie l'API en ligne de commande, sans navigateur.

## Comptes de démo

Deux comptes CloudPics, et une différence qui se voit à l'écran.

| Compte | Mot de passe | Le compte CloudPics | Rôles realm | Rôles dans l'API | GET | POST |
|---|---|---|---|---|---|---|
| `alice` | `alice` | Compte complet : elle consulte et dépose | `PHOTOS_READ`, `PHOTOS_WRITE` | `ROLE_USER`, `ROLE_PHOTOS_READ`, `ROLE_PHOTOS_WRITE` | 200 | 201 |
| `bob` | `bob` | Offre gratuite : lecture seule | `PHOTOS_READ` | `ROLE_USER`, `ROLE_PHOTOS_READ` | 200 | **403** |

Le `403` de bob est la démonstration, et il est le même depuis PhotoPrint et depuis PhotoBook :
l'**autorisation** vit dans CloudPics API, l'**authentification** vit dans CloudPics ID. Changer de
client ne change rien à ce que bob a le droit de faire.

Console d'admin de CloudPics ID : http://localhost:8080 (`admin` / `admin`).

## Le realm `photos`, alias CloudPics ID

Importé au démarrage depuis `keycloak/import/photos-realm.json`. Keycloak tourne en `start-dev`, sur
une base H2 interne au conteneur, et aucun volume n'est monté. Comme `castor stop` fait un
`docker compose down -v`, **chaque cycle repart d'un realm propre** : les comptes, les données et
surtout les **clés de signature** sont neufs.

| `client_id` | Acteur | Type | Flow | Redirect URI |
|---|---|---|---|---|
| `photoprint` | 🌁 PhotoPrint | public | `authorization_code` + PKCE S256 | `http://localhost:5173/*` |
| `photobook` | 📕 PhotoBook | confidentiel (`photobook-secret`) | `authorization_code` + PKCE S256 | `http://localhost:8101/*` |
| `cloudpics-smoke-test` | (outillage) | public | `password` (Direct Access Grants) | aucune |

Ces `client_id` ne sont pas décoratifs : ils apparaissent dans les tokens que vous projetez. L'access
token de PhotoPrint porte `azp: photoprint` et `aud: cloudpics-api`, ce qui se lit d'un coup d'oeil :
**délivré à PhotoPrint, valable pour l'API CloudPics**.

`cloudpics-smoke-test` existe **uniquement** pour `castor smoke`. Le flow `password` est déprécié
(OAuth 2.1) : il n'est pas montré dans le talk.

### Le piège de l'audience

Par défaut, l'audience d'un access token Keycloak est le **client qui l'a demandé** (plus `account`
si ce client a des rôles sur le client `account`). Jamais votre API. Or le token handler `oidc` de
Symfony **valide l'audience** : sans mapper, chaque requête tombe en `401`.

Le realm ajoute donc un protocol mapper `oidc-audience-mapper` sur chaque client, qui injecte
`cloudpics-api` dans le claim `aud`. C'est la valeur attendue par `OIDC_AUDIENCE` dans l'API.

## Les deux apps clientes sont faites pour être projetées

Elles reprennent le design system du deck (`theme/styles/tokens.css`) : mêmes couleurs, même
typographie, fond clair. L'accent est l'indigo `--a-4`, celui de la section « Authentification ».
Un `2xx` s'affiche en teal, un `4xx` en magenta de marque : le `403` de bob est un **refus voulu**,
pas une panne, et la couleur doit le dire.

Deux règles tenues par construction :

- **La page ne défile jamais**, de 1280x720 à 1920x1080. Ce qui déborde (le JSON d'un token, le corps
  d'une réponse) défile *dans* sa carte. Vérifié aux deux extrémités de la plage.
- **Aucune requête réseau** pour l'affichage : pas de webfont distante, rien que le wifi de la salle
  puisse casser.

Trois détails qui servent le propos :

| Détail | Pourquoi |
|---|---|
| Le claim `aud` est colorié dans les deux cartes de tokens | C'est le seul endroit que vous montrez du doigt : `photoprint` d'un côté, `cloudpics-api` de l'autre. À côté, `azp` dit qui a reçu le token. |
| L'access token affiche son temps restant | Ça illustre « les access tokens sont courts », et ça vous prévient avant que la démo ne réponde `401`. |
| Le `401` affiche l'en-tête `WWW-Authenticate` | L'API n'a pas de corps à renvoyer sur un `401` : elle dit `error="invalid_token"` dans l'en-tête. |
| Les deux apps ont un bouton **« avec l'ID token »**, encadré en magenta | Il envoie l'ID token à la place de l'access token, et récolte un `401`. Un jeton parfaitement valide et parfaitement signé, mais dont l'audience est le client, pas l'API. |

Les deux `401` possibles ne disent pas la même chose, et les apps les distinguent : sur le
contre-exemple, le refus **est** la démonstration, donc elles expliquent l'audience. Sur un vrai token
devenu invérifiable, elles proposent « Oublier la session ». Confondre les deux ferait dire à la démo
l'inverse de ce que vous racontez.

Les apps retirent la `trace` PHP des corps d'erreur avant de les afficher. En dev, un `403` d'API
Platform pèse 2,5 ko de chemins de vendor : projeté, ça noie la seule ligne qui compte,
`"detail": "Access Denied."`. Exactement ce que ferait un vrai client.

## Ce qu'il faut regarder dans le code

| Fichier | Ce qu'il montre |
|---|---|
| `api/config/packages/security.yaml` | Le firewall `access_token` + token handler `oidc` (offline). La variante `oidc_user_info` (online) est en commentaire. |
| `api/src/Security/OidcUserProvider.php` | Le mapping `realm_access.roles` -> `ROLE_*`. OIDC n'a aucune notion de rôle : ce mapping est à votre charge. |
| `api/src/Entity/Photo.php` | La ressource API Platform, inchangée : `is_granted('ROLE_USER')` et `is_granted('ROLE_PHOTOS_WRITE')`. |
| `client-spa/src/oidc.ts` | Les quinze lignes qui font de PhotoPrint un client OIDC. PKCE S256 est le défaut de la bibliothèque. |
| `client-spa/src/main.ts` | La distinction ID token (pour PhotoPrint) / access token (pour CloudPics API), et le contre-exemple. |
| `client-symfony/config/packages/drenso_oidc.yaml` | La config du client confidentiel PhotoBook, secret compris. |
| `client-symfony/src/Security/OidcIdentityProvider.php` | PhotoBook n'a besoin que de l'identité. Les rôles restent l'affaire de l'API : la classe est nommée autrement que celle de l'API, exprès. |
| `client-symfony/src/Api/PhotoApiClient.php` | Comment PhotoBook relaie l'access token de la session vers CloudPics API, et `listWithIdToken()` pour le contre-exemple. |

## Basculer offline / online

Dans `api/config/packages/security.yaml`, deux token handlers sont fournis. Le premier est actif,
le second commenté :

- `oidc` : vérifie la **signature** localement, avec les clés publiques du `.well-known`. Zéro appel réseau par requête.
- `oidc_user_info` : appelle le Provider **à chaque requête**. Révocation immédiate, mais l'API tombe si le Provider tombe.

Une différence que le tableau du deck ne montre pas : le handler `oidc_user_info` **ne valide pas
l'audience**. Il présente l'access token au `userinfo` du Provider et lit les claims de la réponse.
Passer en online, c'est donc aussi renoncer au contrôle de `aud` : n'importe quel token valide du realm,
même émis pour une autre API, est accepté. C'est un argument de plus pour l'offline.

Commentez l'un, décommentez l'autre, `castor cc`, et rejouez `castor smoke`.

`castor cc` purge aussi le pool `cache.app`, et ce n'est pas cosmétique : les deux handlers y écrivent
sous **la même clé** de discovery, mais pas le même contenu (le handler `oidc` y met le JWKS, le handler
`oidc_user_info` y met le document de discovery brut). Sans purge, le second lit ce que le premier a
laissé et renvoie `401` sur tous les tokens.

## Un réglage de scène assumé

Le realm porte `accessTokenLifespan: 3600` (une heure), là où Keycloak livre 5 minutes par défaut.
C'est un **confort de scène** : avec 5 minutes, la démo meurt au milieu du talk, et une heure couvre le talk plus les questions. Ce n'est pas une
recommandation, et le deck dit l'inverse : access tokens **courts** plus vérification offline, c'est
le meilleur compromis en production. La session SSO est elle aussi allongée (`ssoSessionIdleTimeout`
à 2 h) pour survivre à une mise en veille du portable.

## Quatre pièges qui coûtent une démo

| Symptôme | Cause | Remède |
|---|---|---|
| Tout répond `401` après un `castor stop` / `castor start` | Keycloak tourne en `start-dev` : il **régénère ses clés de signature** à chaque démarrage, mais l'API garde l'ancien JWKS dans `cache.app`. | `castor start` purge `cache.app` automatiquement. À la main : `php bin/console cache:pool:clear cache.app`. |
| Tout répond `401` juste après avoir basculé offline / online | Les deux token handlers partagent la clé de cache de discovery mais n'y stockent pas la même chose. | `castor cc`. |
| Le navigateur se croit connecté, mais l'API répond `401` | Après un `castor restart`, Keycloak a de nouvelles clés : le token gardé par le navigateur ou par la session Symfony a été signé par l'instance précédente. | Les deux apps le disent et offrent un bouton **« Oublier la session »**. |
| L'horloge du conteneur a dérivé après une veille | Le token handler de Symfony vérifie `iat`, `nbf` et `exp` avec `allowedTimeDrift: 0`, une valeur codée en dur. Une seconde de décalage suffit. | `castor start` et `castor smoke` comparent les deux horloges et vous préviennent. Redémarrer Docker Desktop. |

« Oublier la session » n'est pas un doublon de « Se déconnecter ». La déconnexion normale est une
déconnexion **RP-initiated** : elle envoie un `id_token_hint` au Provider pour fermer aussi la session
SSO. Si Keycloak a redémarré, ce token a été signé par l'instance précédente, et le Provider répond
`400`. « Oublier la session » se contente de jeter l'état local, ce qui est le seul remède sûr dans ce
cas précis.

Dans tous les cas, `demo/api/var/log/dev.log` donne la raison exacte du rejet : le token handler `oidc`
loggue la signature, l'audience, l'issuer ou le claim manquant.

## La démo en ligne, sur Clever Cloud

Déployée dans l'orga `ms-ambassador`, région `par`.

| Acteur | URL publique | Ressource Clever |
|---|---|---|
| 🛂 CloudPics ID | https://qxvnvwdyd6l71qxfrdu2-keycloak.services.clever-cloud.com | add-on `keycloak` 26.7.1, plan BASE (~37 € / 30 j) |
| ☁️ CloudPics API | https://cloudpics-api.cleverapps.io | app `php` nano, build dédié M |
| 🌁 PhotoPrint | https://photoprint-demo.cleverapps.io | app `static` pico, build dédié M |
| 📕 PhotoBook | https://photobook-demo.cleverapps.io | app `php` nano, build dédié M |

Le realm est le **même** qu'en local : ses redirect URI acceptent à la fois `localhost` et les domaines
Clever, donc `castor start` continue de fonctionner sans rien changer.

```bash
clever deploy --alias api     # CloudPics API
clever deploy --alias book    # PhotoBook
clever deploy --alias print   # PhotoPrint
```

Les trois apps facturent en continu. `clever stop --alias api|book|print` entre deux répétitions, et
`clever restart` avant le talk.

### Ce que Clever Cloud demande en plus du local

| Point | Pourquoi |
|---|---|
| `symfony/apache-pack` sur les deux apps PHP | Le runtime PHP sert derrière Apache, et le squelette API Platform ne fournit aucun `.htaccess`. Sans lui, Apache exécute `index.php` mais sans réécrire l'URL : Symfony ne voit jamais le chemin demandé et répond `404` sur toutes les routes. |
| `trusted_proxies` dans les deux `framework.yaml` | Derrière le proxy inverse, Symfony se croit en `http`. PhotoBook générerait un `redirect_uri` en `http` que CloudPics ID refuserait. |
| Le seed dans `api/clever-post-build.sh` | `CC_POST_BUILD_HOOK` s'exécute depuis la **racine du dépôt**, pas depuis `APP_FOLDER`. Un `php bin/console` nu échoue sur « Could not open input file ». |
| `APP_FOLDER` sur les apps PHP, mais **pas** sur l'app statique | Le monorepo se gère avec `APP_FOLDER`. Sur le runtime `static`, cette variable combinée à un webroot profond fait échouer la phase de run : là, on construit depuis la racine avec `CC_BUILD_COMMAND` et `CC_WEBROOT=/demo/client-spa/dist`. |
| Un build dédié (`--build-flavor M`) | Par défaut le build tourne sur l'instance elle-même : un `composer install` d'API Platform ne tient pas dans 512 Mo. |

Le SPA reçoit ses `VITE_*` comme variables d'environnement Clever : Vite leur donne priorité sur les
fichiers `.env`, donc le bundle de production pointe sur les URL publiques sans toucher au dépôt.

### Le realm, côté add-on managé

L'add-on Keycloak de Clever monte un FS Bucket. Le realm et le thème de login s'y déposent, puis on
relance l'instance pour déclencher l'import :

```bash
# Récupérer les identifiants FTP du bucket de l'add-on, puis :
curl --ftp-create-dirs -T keycloak/import/photos-realm.json "ftp://$HOST/realms/import/photos-realm.json" --user "$USER:$PASS"
curl --ftp-create-dirs -T keycloak/themes/photos/login/theme.properties "ftp://$HOST/themes/photos/login/theme.properties" --user "$USER:$PASS"
clever restart --app <app-java-de-l-addon> --without-cache
```

### Deux choses à savoir

**L'admin de CloudPics ID n'est pas `admin/admin`.** L'add-on managé génère son propre compte
`cc-account-admin` avec un mot de passe temporaire, et **impose de le changer à la première
connexion**. C'est la seule différence assumée avec le local. Les comptes de démo, eux, sont
identiques : `alice/alice` et `bob/bob`, importés depuis le realm.

**La démo en ligne est publique.** N'importe qui peut se connecter en alice ou bob et écrire dans
l'API. La base est une SQLite sur disque éphémère, recréée à chaque déploiement : il n'y a rien à
perdre, mais ce n'est pas un environnement à traiter comme durable.
