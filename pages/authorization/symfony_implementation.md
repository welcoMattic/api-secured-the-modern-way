---
layout: default
class: sec-authz
---

# Serveur OAuth2 avec API Platform / Symfony

<v-clicks>

- 📦 `league/oauth2-server-bundle`
- 🏛️ Votre API = **authorization server + resource server**
- 🎫 Émission + validation des tokens, contrôle des **scopes**

</v-clicks>

---
layout: default
class: sec-authz
---

# Configuration minimale

```yaml
# config/packages/league_oauth2_server.yaml
league_oauth2_server:
    authorization_server:
        private_key: '%kernel.project_dir%/var/oauth/private.key'
        encryption_key: 'def00000examplekey1234567890ab'
    resource_server:
        public_key: '%kernel.project_dir%/var/oauth/public.key'
    scopes: { available: [PHOTOS_READ, PHOTOS_WRITE], default: [PHOTOS_READ] }
    persistence: { doctrine: ~ }
```

<div class="mb-4"></div>

<div class="grid grid-cols-3 gap-4" v-click>

```yaml
# config/packages/security.yaml
security:
    firewalls:
        api_token:
            pattern: ^/token$
            security: false
        main:
            pattern: ^/api
            stateless: true
            oauth2: true
```

<div class="col-span-2" v-click="2">

```yaml
# config/routes.yaml
oauth2:
    resource: '@LeagueOAuth2ServerBundle/config/routes.php'
    type: php
```

</div>

</div>

---
layout: default
class: sec-authz
---

# Créer un client OAuth2

En ligne de commande

```shell
php bin/console league:oauth2-server:create-client \
    --grant-type authorization_code \
    --redirect-uri https://photoprint.example/callback \
    --scope PHOTOS_READ \
    --public \
    PhotoPrint
```

<v-click>

<Alert type="info">

Clients et tokens **persistés par le bundle**. `--public` : PhotoPrint est une SPA sans secret, le bundle lui **impose PKCE** par défaut.

</Alert>

</v-click>

---
layout: default
class: sec-authz
---

# Durée de vie des tokens

```yaml
# config/packages/league_oauth2_server.yaml
league_oauth2_server:
    authorization_server:
        access_token_ttl: PT1H   # 1 heure (défaut)
        refresh_token_ttl: P1M   # 1 mois (défaut)
        enable_refresh_token_grant: true
```

<v-clicks>

- ⏳ **Access token court** : fenêtre d'exploitation réduite
- ♻️ **Refresh token** : renouvelle sans l'utilisateur
- 🔄 **Rotation** : refresh utilisé = révoqué

</v-clicks>

---
layout: default
class: sec-authz
---

# API Platform applique les scopes du token

```php
// src/Entity/Photo.php
#[ApiResource(security: "is_granted('ROLE_OAUTH2_PHOTOS_READ')")]
#[GetCollection]
#[Post(security: "is_granted('ROLE_OAUTH2_PHOTOS_WRITE')")]
class Photo
{
    // ...
}
```

<div class="slide-note">Le bundle transforme chaque scope en rôle : <code>ROLE_OAUTH2_</code> + le scope en majuscules.</div>

---
layout: default
class: sec-authz
---

# Le bundle ne fait pas tout à votre place

<v-clicks>

- 👤 **Login d'Alice** : `/authorize` exige un utilisateur connecté, le formulaire de login reste à coder
- 🙋 **Consentement** : le bundle émet un événement, l'écran « Autorises-tu PhotoPrint ? » reste à coder
- 🔑 **Rotation** des clés de signature
- ⛓️ **MFA**, mot de passe oublié, révocation de sessions
- 📊 **Audit** : qui a autorisé quoi, et quand

</v-clicks>

---
layout: statement
class: sec-authz
---

# Vous maintenez un serveur d'autorisation

C'est un **produit à part entière**. <br>
Trop lié à votre API : l'un **peut faire tomber** l'autre
