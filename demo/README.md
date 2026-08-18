# Démo : déléguer l'authentification à un OIDC Provider

Démo compagnon du talk **API Secured, the Modern Way** (API Platform Con 2026).

Elle montre **une seule idée** : l'API n'authentifie plus personne. Un OIDC Provider (Keycloak)
émet les tokens, l'API se contente de les **vérifier** avec les token handlers **natifs** de Symfony.

La partie « API Platform comme serveur d'autorisation OAuth2 » (`league/oauth2-server-bundle`)
est volontairement hors périmètre.

## Les quatre briques

| Brique | Rôle | Techno | URL |
|---|---|---|---|
| `keycloak/` | OIDC Provider : comptes, login, consentement, émission des tokens | Keycloak 26 (Docker) | http://localhost:8080 |
| `api/` | Resource server : vérifie les access tokens, applique les rôles | API Platform 4 / Symfony 8.1 | http://localhost:8100 |
| `client-spa/` | App cliente JS : `authorization_code` + PKCE dans le navigateur | Vite + TypeScript + `oidc-client-ts` | http://localhost:5173 |
| `client-symfony/` | App cliente Symfony : `authorization_code` côté serveur | Symfony 8.1 + `drenso/symfony-oidc-bundle` | http://localhost:8101 |

```
  client-spa (JS)  ─┐                        ┌─ authorization_code + PKCE ─→ Keycloak
                    ├─ Bearer access_token ─→ API Platform ─ discovery .well-known ─→ Keycloak
  client-symfony ──┘                        └─ vérification offline (signature RS256)
```

## Les deux chemins d'authentification

| | App cliente JS (`client-spa`) | App cliente Symfony (`client-symfony`) |
|---|---|---|
| **Qui initie le flow ?** | Le navigateur, via `oidc-client-ts` | Le serveur, via `drenso/symfony-oidc-bundle` |
| **Client OIDC** | Public + PKCE S256 | Confidentiel (client secret) |
| **Ce que Symfony fournit nativement** | Rien côté client : c'est du JS | Pas encore le flow `authorization_code` ([PR #64954](https://github.com/symfony/symfony/pull/64954)) |
| **Côté API** | `access_token` + token handler `oidc` (natif) | Identique : le même firewall, le même handler |

Le point clé : **côté API, rien ne change**. Le resource server ne sait pas quel type de client lui parle.

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
castor start     # keycloak + api + client-symfony + client-spa
castor open      # ouvre les 3 apps et la console Keycloak
```

`castor stop` arrête tout. `castor smoke` vérifie l'API en ligne de commande, sans navigateur.

## Comptes de démo

| Utilisateur | Mot de passe | Rôles realm Keycloak | Rôles Symfony dans l'API | GET /api/photos | POST /api/photos |
|---|---|---|---|---|---|
| `alice` | `alice` | `PHOTOS_READ`, `PHOTOS_WRITE` | `ROLE_USER`, `ROLE_PHOTOS_READ`, `ROLE_PHOTOS_WRITE` | 200 | 201 |
| `bob` | `bob` | `PHOTOS_READ` | `ROLE_USER`, `ROLE_PHOTOS_READ` | 200 | **403** |

Le `403` de bob est la démonstration : l'autorisation vit dans l'API, l'authentification vit dans le Provider.

Console d'admin Keycloak : http://localhost:8080 (`admin` / `admin`).

## Le realm `photos`

Importé au démarrage depuis `keycloak/import/photos-realm.json`. Keycloak tourne en `start-dev`, sur
une base H2 interne au conteneur, et aucun volume n'est monté. Comme `castor stop` fait un
`docker compose down -v`, **chaque cycle repart d'un realm propre** : les comptes, les données et
surtout les **clés de signature** sont neufs.

| Client | Type | Flow | Redirect URI |
|---|---|---|---|
| `photos-spa` | public | `authorization_code` + PKCE S256 | `http://localhost:5173/*` |
| `photos-symfony` | confidentiel (`photos-symfony-secret`) | `authorization_code` | `http://localhost:8101/*` |
| `photos-smoke-test` | public | `password` (Direct Access Grants) | aucune |

`photos-smoke-test` existe **uniquement** pour `castor smoke`. Le flow `password` est déprécié
(OAuth 2.1) : il n'est pas montré dans le talk.

### Le piège de l'audience

Par défaut, l'audience d'un access token Keycloak est le **client qui l'a demandé** (plus `account`
si ce client a des rôles sur le client `account`). Jamais votre API. Or le token handler `oidc` de
Symfony **valide l'audience** : sans mapper, chaque requête tombe en `401`.

Le realm ajoute donc un protocol mapper `oidc-audience-mapper` sur chaque client, qui injecte
`api-photos` dans le claim `aud`. C'est la valeur attendue par `OIDC_AUDIENCE` dans l'API.

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
| Le claim `aud` est colorié dans les deux cartes de tokens | C'est le seul endroit que vous montrez du doigt : `photos-spa` d'un côté, `api-photos` de l'autre. |
| L'access token affiche son temps restant | Ça illustre « les access tokens sont courts », et ça vous prévient avant que la démo ne réponde `401`. |
| Le `401` affiche l'en-tête `WWW-Authenticate` | L'API n'a pas de corps à renvoyer sur un `401` : elle dit `error="invalid_token"` dans l'en-tête. |

Les apps retirent la `trace` PHP des corps d'erreur avant de les afficher. En dev, un `403` d'API
Platform pèse 2,5 ko de chemins de vendor : projeté, ça noie la seule ligne qui compte,
`"detail": "Access Denied."`. Exactement ce que ferait un vrai client.

## Ce qu'il faut regarder dans le code

| Fichier | Ce qu'il montre |
|---|---|
| `api/config/packages/security.yaml` | Le firewall `access_token` + token handler `oidc` (offline). La variante `oidc_user_info` (online) est en commentaire. |
| `api/src/Security/OidcUserProvider.php` | Le mapping `realm_access.roles` -> `ROLE_*`. OIDC n'a aucune notion de rôle : ce mapping est à votre charge. |
| `api/src/Entity/Photo.php` | La ressource API Platform, inchangée : `is_granted('ROLE_USER')` et `is_granted('ROLE_PHOTOS_WRITE')`. |
| `client-spa/src/main.ts` | `authorization_code` + PKCE, et la distinction ID token (pour le client) / access token (pour l'API). |
| `client-symfony/config/packages/drenso_oidc.yaml` | La config du client OIDC côté serveur. |
| `client-symfony/src/Security/OidcUserProvider.php` | Le client Symfony n'a besoin que de l'identité : les rôles restent l'affaire de l'API. |

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
