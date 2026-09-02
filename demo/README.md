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
| 📕 **PhotoBook** | Client **confidentiel** : tourne sur son serveur, lui peut garder un secret | `client-symfony/` | Symfony 8.2-dev + authenticator natif `oidc_login` | http://localhost:8101 |

PhotoBook tourne sur Symfony 8.2 de développement, parce que l'authenticator `oidc_login` y est mergé
([PR 64954](https://github.com/symfony/symfony/pull/64954)) mais que 8.2 ne sort qu'en novembre 2026.
Tout vient de packagist en `8.2.x-dev`, y compris `symfony/security-bundle`, `symfony/security-core` et
`symfony/security-http` : plus aucun dépôt snapshot, plus aucune entrée `repositories`.
`web-token/jwt-library` s'ajoute au passage : c'est lui qui décode l'ID token, ici comme dans l'API.

Le RP-Initiated Logout ne fait pas partie de ce qui a été mergé : `/logout` ferme la session de
PhotoBook, pas celle de CloudPics ID.

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
| **Qui initie le flow ?** | Le navigateur, via `oidc-client-ts` | Le serveur, via l'authenticator natif `oidc_login` |
| **Client OIDC** | Public + PKCE S256 | Confidentiel (`client_secret`) + PKCE |
| **Ce que Symfony fournit nativement** | Rien côté client : c'est du JS | Le flow `authorization_code` complet, via `oidc_login` ([PR #64954](https://github.com/symfony/symfony/pull/64954), pas encore mergée) |
| **Côté API** | `access_token` + token handler `oidc` (natif) | Identique : le même firewall, le même handler |

Le point clé : **côté CloudPics API, rien ne change**. Le resource server ne sait pas quel type de
client lui parle, et il n'a pas à le savoir.

### Le beat à ne pas rater sur scène

Connectez-vous d'abord sur PhotoPrint, puis ouvrez PhotoBook et cliquez sur « Se connecter ».
**Aucun écran de login n'apparaît** : la session est déjà ouverte chez CloudPics ID. Les deux apps
affichent alors le **même `sub`**, et chacune a reçu ses propres tokens.

Le mot de passe n'est demandé qu'une fois, et jamais par les apps.

> **Pas d'écran de consentement, et c'est volontaire.** Les deux clients portent
> `consentRequired: false`. L'écran listait « Voir vos photos » et « Déposer des photos », les deux
> scopes que l'app demande, et il les listait **à l'identique pour bob**, qui repart pourtant sans
> `photos:write`. Normal : le consentement est une notion de client, le filtrage par rôle une notion
> d'utilisateur, et Keycloak n'applique les `scopeMappings` qu'en fabriquant le token. Sauf que devant
> une salle, un bob qui coche « Déposer des photos » puis récolte un `403` se lit comme un bug. La
> démonstration de l'intersection vit dans les tokens projetés, pas dans cet écran : on le retire.

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

`castor stop` arrête tout. `castor smoke` vérifie l'API en ligne de commande, sans navigateur, et
`castor test` lance les suites PHPUnit des deux applications PHP, sans rien démarrer du tout.

## Comptes de démo

Deux comptes CloudPics, et une différence qui se voit à l'écran. Les deux apps demandent les **mêmes**
scopes pour l'un comme pour l'autre, `photos:read` et `photos:write` : ce qui les distingue à l'arrivée,
c'est ce que CloudPics ID accorde.

| Compte | Mot de passe | Le compte CloudPics | Rôles realm | Scopes accordés | Rôles dans l'API | GET | POST | Ce qu'il voit |
|---|---|---|---|---|---|---|---|---|
| `alice` | `alice` | Compte complet : elle consulte et dépose | `PHOTOS_READ`, `PHOTOS_WRITE` | `photos:read`, `photos:write` | `ROLE_USER`, `ROLE_PHOTOS_READ`, `ROLE_PHOTOS_WRITE` | 200 | 201 | Ses 2 photos |
| `bob` | `bob` | Offre gratuite : lecture seule | `PHOTOS_READ` | `photos:read` | `ROLE_USER`, `ROLE_PHOTOS_READ` | 200 | **403** | Sa 1 photo |

Bob a bien demandé `photos:write`, comme alice. Le Provider ne le lui a accordé **ni en scope, ni en
rôle**, parce qu'il ne détient pas `PHOTOS_WRITE`. Projetez les deux access tokens côte à côte : c'est
la ligne `scope` qui le dit avant la ligne `realm_access.roles`.

Le cloisonnement par propriétaire et l'autorisation par rôle sont **deux mécanismes distincts** :
l'autorisation par rôle (`ROLE_PHOTOS_WRITE`) dit ce qu'on a le droit de faire (POST pour alice, pas pour bob),
le cloisonnement par propriétaire dit sur quoi (chaque utilisateur ne voit que ses photos, et un accès
à la photo d'un autre retourne 403).

Le `403` de bob sur POST est la démonstration de l'autorisation, et le `403` sur GET /api/photos/{id d'Alice}
est la démonstration du cloisonnement. Les deux sont visibles depuis PhotoPrint et PhotoBook :
l'**autorisation** et le **cloisonnement** vivent dans CloudPics API, l'**authentification** vit dans CloudPics ID.
Changer de client ne change rien à ces règles.

Console d'admin de CloudPics ID : http://localhost:8080 (`admin` / `admin`).

## Rôles et scopes : c'est le Provider qui croise, pas l'API

Deux claims, et il faut les deux pour savoir ce qu'une requête a le droit de faire :

- `realm_access.roles` dit ce qu'**Alice** a le droit de faire ;
- `scope` dit ce qu'Alice a autorisé **cette app** à faire en son nom.

Un access token ne doit porter que l'**intersection** des deux, et cette intersection est faite par
l'autorisation server, pas par le resource server. C'est le modèle standard : le token représente
l'autorité réellement déléguée, et RFC 9068 le dit à sa façon, *« all the individual scope strings in
the "scope" claim MUST have meaning for the resources indicated in the "aud" claim »*. Auth0 l'a
productisé : avec RBAC, le claim `scope` du token est l'intersection des permissions demandées et des
permissions de l'utilisateur.

Chez Keycloak, deux réglages y suffisent, et ils sont dans `keycloak/import/photos-realm.json` :

| Réglage | Effet |
|---|---|
| `"fullScopeAllowed": false` sur chaque client | Sans lui, Keycloak met **tous** les rôles de l'utilisateur dans **tous** ses tokens, quoi que le client ait demandé. |
| `scopeMappings` au niveau du realm | Rattache le rôle `PHOTOS_READ` au client scope `photos:read`, et `PHOTOS_WRITE` à `photos:write`. Ces client scopes étant `optionalClientScopes`, le rôle n'entre dans le token que si le client a demandé le scope **et** que l'utilisateur détient le rôle. |

Ce que ça donne, vérifiable en une commande avec le client `cloudpics-smoke-test` :

| Compte | Scope demandé | `scope` du token | `realm_access.roles` |
|---|---|---|---|
| alice | `openid` | `openid email profile` | *(aucun)* |
| alice | `openid photos:read` | `... photos:read` | `PHOTOS_READ` |
| alice | `openid photos:read photos:write` | `... photos:read photos:write` | `PHOTOS_READ`, `PHOTOS_WRITE` |
| bob | `openid photos:read photos:write` | `... photos:read` | `PHOTOS_READ` |

Deux conséquences pour l'API. La première : `OidcUserProvider::mapRoles()` n'a plus rien à croiser, il
lit un claim et préfixe. La seconde, moins évidente : **le mapping fonctionne à l'identique offline et
online**. Si l'intersection vivait dans le resource server, elle mourrait en online, parce que le
endpoint `userinfo` ne renvoie aucun claim `scope`.

Effet de bord bienvenu : sans les rôles par défaut du realm, `aud` ne vaut plus que `cloudpics-api`,
sans le `account` que Keycloak y ajoutait. La carte de token projetée y gagne.

## La doc OpenAPI décrit aussi l'authentification

Swagger UI : http://localhost:8100/api/docs. Spec brute :
`php bin/console api:openapi:export --yaml` depuis `api/`.

La spec déclare deux schémas de sécurité, et rien d'autre :

| Schéma | Type OpenAPI | Pour qui |
|---|---|---|
| `oauth` | `oauth2`, flow `authorizationCode` + PKCE | Swagger UI, et tout client généré qui doit négocier un token |
| `bearer` | `http`, `bearerFormat: JWT` | Ceux qui détiennent déjà un access token : un `curl`, un job de CI |

Le bouton « Authorize » se sert du client Keycloak public `cloudpics-docs`, avec PKCE S256, et
redirige vers `/bundles/apiplatform/swagger-ui/oauth2-redirect.html`. Le realm étant réimporté à
chaque démarrage du conteneur (voir plus bas), un `castor restart` suffit pour que ce client
existe. Sans lui, « Authorize » répondrait `invalid_client`.

Le flow déclare quatre scopes, dont `photos:read` et `photos:write` : ce sont ceux qu'un client
demande, et Swagger UI doit pouvoir les cocher pour obtenir un token utilisable. Les **rôles**
`PHOTOS_READ` / `PHOTOS_WRITE`, eux, ne sont pas des scopes et n'apparaissent pas dans le flow : la
spec les mentionne en prose dans la `description` de chaque opération, là où un générateur de clients
ne risque pas de les confondre avec quelque chose à demander au Provider.

## Le realm `photos`, alias CloudPics ID

Importé au démarrage depuis `keycloak/import/photos-realm.json`. Keycloak tourne en `start-dev`, sur
une base H2 interne au conteneur, et aucun volume n'est monté. Comme `castor stop` fait un
`docker compose down -v`, **chaque cycle repart d'un realm propre** : les comptes, les données et
surtout les **clés de signature** sont neufs.

| `client_id` | Acteur | Type | Flow | Redirect URI |
|---|---|---|---|---|
| `photoprint` | 🌁 PhotoPrint | public | `authorization_code` + PKCE S256 | `http://localhost:5173/*` |
| `photobook` | 📕 PhotoBook | confidentiel (`photobook-secret`) | `authorization_code` + PKCE S256 | `http://localhost:8101/*` |
| `cloudpics-docs` | 📗 Swagger UI de l'API | public | `authorization_code` + PKCE S256 | `http://localhost:8100/*` |
| `cloudpics-smoke-test` | (outillage) | public | `password` (Direct Access Grants) | aucune |

Les quatre portent `"fullScopeAllowed": false` et les deux client scopes `photos:read` / `photos:write`
en optionnels : c'est ce qui fait de `realm_access.roles` une intersection et non un décalque des rôles
du compte. Voir « Rôles et scopes » plus haut.

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

Avec `fullScopeAllowed: false`, le `account` disparaît au passage : les rôles par défaut du realm ne
sont plus dans le token, donc le mapper `audience resolve` n'a plus de client à y ajouter. `aud` vaut
exactement `cloudpics-api`, ce qui rend la carte de token projetée plus lisible qu'un tableau à deux
entrées.

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

Quelques détails qui servent le propos. Les deux premiers ne valent que pour PhotoPrint : PhotoBook
n'affiche pas de cartes de tokens, il affiche l'identité que le provider natif `oidc` lui a fabriquée.

| Détail | Pourquoi |
|---|---|
| Le claim `aud` est colorié dans les deux cartes de tokens de PhotoPrint | C'est le seul endroit que vous montrez du doigt : `photoprint` d'un côté, `cloudpics-api` de l'autre. À côté, `azp` dit qui a reçu le token. |
| La carte d'access token montre `scope` juste avant `realm_access.roles` | Ce que l'app a demandé, puis ce que le Provider a accordé. Sur le compte de bob, la deuxième ligne est plus courte que la première : c'est l'intersection, à l'écran. |
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
| `api/src/Security/OidcUserProvider.php` | Le mapping `realm_access.roles` -> `ROLE_*`. OIDC n'a aucune notion de rôle : ce mapping est à votre charge. Six lignes, parce que l'intersection rôle / scope a déjà été faite par le Provider. |
| `api/src/Entity/Photo.php` | La ressource API Platform avec la propriété `owner`, et l'expression de sécurité sur l'item qui vérifie le rôle **puis** le propriétaire. L'expression d'une opération remplace celle de la ressource : oublier le rôle ici, c'est ne plus le vérifier du tout. |
| `api/src/State/PhotoOwnerProcessor.php` | Le state processor qui impose le propriétaire côté serveur depuis l'utilisateur authentifié au POST, sans que le client ne puisse le choisir. |
| `api/src/Doctrine/PhotoOwnerExtension.php` | L'extension Doctrine qui filtre la collection sur le propriétaire, démontrant le cloisonnement. |
| `client-spa/src/oidc.ts` | Les quinze lignes qui font de PhotoPrint un client OIDC. PKCE S256 est le défaut de la bibliothèque, et les scopes `photos:*` sont ce que l'app demande à Alice de lui déléguer. |
| `client-symfony/config/packages/security.yaml` | Tout PhotoBook tient là : le firewall `oidc_login` natif (issuer, client confidentiel, scopes, PKCE S256 par défaut, RP-Initiated Logout) et le provider natif `oidc`, qui ne donne que `ROLE_USER`. Les rôles restent l'affaire de l'API. |
| `client-symfony/config/routes/security.yaml` | L'import du route loader qui déclare la route du `check_path`. Sans lui, le retour du Provider tombe sur un 404 du routeur. |
| `client-symfony/src/Api/PhotoApiClient.php` | Comment PhotoBook relaie l'access token de la session vers CloudPics API, et `listWithIdToken()` pour le contre-exemple. |

## Les tests

`castor test` lance les deux suites. Elles ne demandent **ni Docker, ni Keycloak, ni serveur** :
CloudPics ID et CloudPics API sont simulés au niveau du transport HTTP, via
`framework.http_client.mock_response_factory`. Autrement dit, aucun service de sécurité n'est
remplacé : le firewall, le token handler `oidc` et sa discovery sont ceux qui tournent sur scène.

| Suite | Ce qu'elle prouve |
|---|---|
| `api/tests/Api/PhotoSecurityTest.php` | Les refus et les accès de CloudPics API, cas par cas : pas de token, token qui n'est pas un JWT, ID token refusé sur son audience, signature d'un autre émetteur, autre issuer, token expiré, authentifié mais sans rôle, cloisonnement par propriétaire, 403 de bob sur POST, propriétaire imposé au POST même si le client tente de le choisir, et `/api/docs` toujours lisible sans token. |
| `api/tests/Security/OidcUserProviderTest.php` | Le mapping des claims vers des `ROLE_*`, y compris le cas « aucun rôle accordé » et celui d'un rôle realm que l'API ne connaît pas. |
| `client-symfony/tests/Api/PhotoApiClientTest.php` | Le jeton que PhotoBook envoie vraiment (access token, puis ID token sur le contre-exemple), le retrait de la `trace` PHP, la remontée du `WWW-Authenticate`, et une API injoignable qui ne lève aucune exception. |
| `client-symfony/tests/Controller/PhotoControllerTest.php` | Les trois boutons, du clic à l'affichage, contre-exemple compris : la vue explique l'audience au lieu de proposer d'oublier la session. |
| `client-symfony/tests/Controller/SecurityControllerTest.php` | « Oublier la session » face à « Se déconnecter », et la présence de la route du `check_path`. |

Deux détails qui évitent des faux échecs, et qui sont documentés là où ils vivent :

- `api/config/packages/test/cache.yaml` met `cache.app` en mémoire. Sur disque, le JWKS découvert
  survivrait d'un run au suivant, alors que la paire de clés de test est régénérée à chaque processus :
  tous les tokens tomberaient en `401` au deuxième run. C'est le même piège que celui de la bascule
  offline / online, en plus discret.
- Aucune clé privée n'est commitée. `api/tests/Oidc/TestKeys.php` génère la paire RS256 du faux
  Provider au premier appel, et une seconde paire « imposteur » pour le seul test qui doit échouer.

`castor smoke` reste complémentaire : lui parle à la vraie stack, en HTTP, et c'est ce qui valide le
realm, les horloges et la bascule du token handler.

## Basculer offline / online

Dans `api/config/packages/security.yaml`, deux token handlers sont fournis. Le premier est actif,
le second commenté :

- `oidc` : vérifie la **signature** localement, avec les clés publiques du `.well-known`. Zéro appel réseau par requête.
- `oidc_user_info` : appelle le Provider **à chaque requête**. Révocation immédiate, mais l'API tombe si le Provider tombe.

Le handler `oidc_user_info` **ne valide pas l'audience**. Il présente l'access token au `userinfo` du
Provider et lit les claims de la réponse. Passer en online, c'est donc renoncer au contrôle de `aud` :
n'importe quel token valide du realm, même émis pour une autre API, est accepté. RFC 9068 est pourtant
catégorique, *« the resource server MUST validate that the "aud" claim contains a resource indicator
value corresponding to an identifier the resource server expects for itself »*.

Il ne voit pas non plus le claim `scope` : `userinfo` décrit l'utilisateur, pas l'autorisation accordée.
Ici ça ne coûte rien, puisque l'intersection est faite par le Provider et que le mapper
`realm-roles-userinfo` place `realm_access.roles` dans la réponse `userinfo`. Mais une API qui aurait
mis l'intersection dans son resource server tomberait à `ROLE_USER` en basculant online, sans rien voir
venir. Le vrai pendant online d'une vérification de token, celui qui renvoie `scope`, `aud` et
`client_id`, c'est l'introspection (RFC 7662).

Symfony fournit d'ailleurs un handler `oauth2` pour l'introspection, mais il ne remplace pas
`oidc_user_info` ici : il fabrique un `OAuth2User` sans passer par le user provider configuré (le
`UserBadge` qu'il retourne porte déjà un loader, et `AccessTokenAuthenticator` ne le remplace que si
c'est un `FallbackUserLoader`). Donc pas de mapping de rôles, et pas de démo.

Commentez l'un, décommentez l'autre, `castor cc`, et rejouez `castor smoke` : les huit tests passent
dans les deux modes.

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

> **Renommer une variable d'environnement casse le déploiement en silence.** `clever deploy` pousse le
> code, jamais la configuration : les variables vivent sur l'app, et ni le déploiement ni un `tofu
> apply` sur un autre fichier ne les synchronise. Or un `%env(FOO)%` que l'app ne définit pas retombe
> sur le `.env` du dépôt, c'est-à-dire sur `localhost`, sans lever la moindre erreur. C'est ce qui est
> arrivé en passant de `OIDC_WELL_KNOWN_URL` à `OIDC_ISSUER` : PhotoBook cherchait son Provider sur
> `http://localhost:8080` depuis Clever, et `/login` répondait `401` au lieu de rediriger, parce que le
> point d'entrée du firewall n'avait aucun `authorization_endpoint` à viser. Après toute modification
> d'un nom de variable, comparer :
>
> ```bash
> clever env --alias book | grep OIDC     # ce que l'app définit
> grep OIDC client-symfony/.env infra/main.tf   # ce que le code attend
> ```
>
> Puis `clever env set NOM valeur --alias book` et `clever env rm ANCIEN_NOM --alias book`.

### Tout recréer avec OpenTofu

Les commandes ci-dessus déploient sur une infra qui existe déjà. Pour la recréer de zéro, le module
d'`infra/` décrit les quatre acteurs en HCL : l'add-on Keycloak, les deux apps PHP, l'app statique,
leurs variables d'environnement. Aucun domaine n'est à réserver : Clever en attribue un à chaque app,
et `tofu output` les donne après l'apply.

```bash
cd infra
cp terraform.tfvars.example terraform.tfvars
tofu init && tofu apply
```

L'apply crée l'infra **et** déploie le code, parce que chaque app porte un bloc `deployment` qui pousse
le HEAD du dépôt public. Seul l'import du realm reste à part : le provider n'expose pas les identifiants
FTP du FS Bucket de l'add-on, donc c'est `infra/import-realm.sh`, une fois.

Détails, mise à jour du realm avec les domaines générés, CORS et coût : [`infra/README.md`](infra/README.md).

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
