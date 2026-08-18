---
layout: fact
class: sec-authn
---

## Contrairement au bundle serveur OAuth2, <br> nous n'implémentons pas notre propre OIDC Provider.

---
layout: default
class: sec-authn
---

# OIDC Providers (OP) Open Source

<ServiceGroup europe label="Europe" :cols="4" class="mt-3">
  <Logo :size="2.4" src="/authelia.png" label="Authelia" />
  <Logo :size="2.4" src="/goauthentik.png" label="Authentik" />
  <Logo :size="2.4" src="/ferriskey.png" label="FerrisKey" />
  <Logo :size="2.4" src="/gravitee.webp" label="Gravitee AM" />
  <Logo :size="2.4" src="/ory-hydra.png" label="Ory Hydra" />
  <Logo :size="2.4" src="/pocketid.png" label="PocketID" />
  <Logo :size="2.4" src="/rauthy.png" label="Rauthy" />
  <Logo :size="2.4" src="/zitadel.png" label="Zitadel" />
</ServiceGroup>

<ServiceGroup label="Reste du monde" :cols="4" class="mt-3">
  <Logo :size="2.4" src="/casdoor.png" label="Casdoor" />
  <Logo :size="2.4" src="/dex.svg" label="Dex" />
  <Logo :size="2.4" src="/keycloak.png" label="Keycloak" />
  <Logo :size="2.4" src="/supertokens.png" label="SuperTokens" />
</ServiceGroup>

<div class="text-center text-sm mt-4" style="color:var(--c-muted)">
  <strong style="color:var(--c-fg)">Ils parlent tous la même langue :</strong> le protocole OIDC
</div>

---
layout: default
class: sec-authn
---

# OIDC Providers SaaS

<ServiceGroup europe label="Europe" :cols="4" class="mt-3">
  <Logo :size="2.4" src="/cidaas.png" label="Cidaas" />
  <Logo :size="2.4" src="/cloud-iam.png" label="Cloud-IAM" />
  <Logo :size="2.4" src="/gravitee.webp" label="Gravitee AM" />
  <Logo :size="2.4" src="/zitadel.png" label="Zitadel" />
</ServiceGroup>

<ServiceGroup label="Reste du monde" :cols="5" class="mt-3">
  <Logo :size="2.4" src="/auth0.png" label="Auth0" />
  <Logo :size="2.4" src="/aws-cognito.png" label="AWS Cognito" />
  <Logo :size="2.4" src="/clerk.png" label="Clerk" />
  <Logo :size="2.4" src="/entra_id.png" label="Microsoft Entra ID <br/> <small>(formerly Azure AD)</small>" />
  <Logo :size="2.4" src="/firebase.png" label="Firebase Auth" />
  <Logo :size="2.4" src="/kinde.png" label="Kinde" />
  <Logo :size="2.4" src="/loginradius.png" label="LoginRadius" />
  <Logo :size="2.4" src="/okta.png" label="Okta" />
  <Logo :size="2.4" src="/pingone.svg" label="PingOne" />
  <Logo :size="2.4" src="/supertokens.png" label="SuperTokens" />
</ServiceGroup>

<div class="text-center text-sm mt-4" style="color:var(--c-muted)">
  <strong style="color:var(--c-fg)">Ils parlent tous la même langue :</strong> le protocole OIDC
</div>

---
layout: default
class: sec-authn
---

# Besoin de SSO social ? {.!mb-12}

### **SSO social natif** : quelques client credentials à configurer.

<LogoGrid :gapX="5" class="pt-12">
  <Logo src="/google.svg" label="Google" />
  <Logo src="/facebook.svg" label="Facebook" />
  <Logo src="/apple.svg" label="Apple" />
  <Logo src="/github.png" label="GitHub" />
</LogoGrid>

---
layout: default
class: sec-authn
---

# Votre API redevient un simple resource server

<v-clicks>

- 👉 Les **apps clientes** redirigent les utilisateurs vers l'**OIDC Provider** 
- 🛂 L'**OIDC Provider** les authentifient et **émet les tokens**
- 👥 Les **comptes** vivent dans l'**OIDC Provider**, plus dans votre base
- 🧩 Votre **API** doit **vérifier** les tokens
- 🚫 Aucun écran de login ni de consentement à coder
- 🏦 Votre **API** se concentre sur le métier

</v-clicks>

---
layout: default
class: sec-authn
---

# Symfony vérifie les access tokens nativement

<v-clicks>

- 🔌 Authenticator **`access_token`** dans le firewall
- 📥 Lit l'en-tête **`Authorization: Bearer`** par défaut
- 🧩 Un **token handler** décide *comment* valider
- 🎯 Deux handlers OIDC natifs : **`oidc`** et **`oidc_user_info`**

</v-clicks>

---
layout: default
class: sec-authn
---

# Vérification offline : la signature suffit

```yaml
# config/packages/security.yaml
security:
    firewalls:
        api:
            pattern: ^/api
            stateless: true
            access_token:
                token_handler:
                    oidc:
                        algorithms: ['RS256']
                        audience: 'api-photos'
                        issuers: ['https://id.example.com/realms/photos']
                        discovery:
                            base_uri: 'https://id.example.com/realms/photos/'
                            cache: { id: cache.app }
```

<v-click>

<Alert type="info">

`composer require web-token/jwt-library`. <br/> Les clés publiques viennent du `.well-known` : vérification en local.

</Alert>

</v-click>

---
layout: default
class: sec-authn
---

# Vérification online : on interroge le Provider

```yaml
# config/packages/security.yaml
security:
    firewalls:
        api:
            pattern: ^/api
            stateless: true
            access_token:
                token_handler:
                    oidc_user_info:
                        base_uri: 'https://id.example.com/realms/photos/'
                        claim: email
                        discovery:
                            cache: { id: cache.app }
```

<v-click>

<Alert type="info">

`composer require symfony/http-client`. <br/> Un appel HTTP au Provider à **chaque requête** entrante.

</Alert>

</v-click>

---
layout: default
class: sec-authn
---

# Offline ou online : un arbitrage, pas un gagnant

|                          | `oidc` (offline)          | `oidc_user_info` (online) |
|--------------------------|---------------------------|---------------------------|
| **Appel réseau**         | Aucun                     | Un par requête            |
| **Révocation d'un token**| Visible à l'expiration    | Immédiate                 |
| **Provider indisponible**| L'API continue de servir  | L'API ne répond plus      |
| **Dépendance**           | `web-token/jwt-library`   | `symfony/http-client`     |

<v-click>

#### Access tokens courts + offline : le meilleur compromis dans la majorité des cas. {.mt-6}

</v-click>

---
layout: default
class: sec-authn
---

# OIDC ne standardise pas la notion de rôle

<v-clicks>

- 🪪 `OidcUser` par défaut : **`ROLE_USER`**, et rien d'autre
- 🧾 Le claim porteur des rôles varie : `realm_access`, `groups`, `scope`...
- 🔁 À vous de **mapper** les claims vers des rôles Symfony

</v-clicks>

<v-click>

<Alert type="warning">

Le code de Symfony le dit explicitement : les specs OIDC et OAuth n'ont **aucune** notion de rôle.

</Alert>

</v-click>

---
layout: default
class: sec-authn
---

# Le mapping vit dans un UserProvider dédié

```php
// src/Security/OidcUserProvider.php
class OidcUserProvider implements AttributesBasedUserProviderInterface
{
    public function loadUserByIdentifier(string $id, array $attributes = []): UserInterface
    {
        return new OidcUser(
            userIdentifier: $id,
            roles: $this->mapRoles($attributes), // claims -> ROLE_*
            sub: $attributes['sub'],
            email: $attributes['email'] ?? null,
        );
    }

    private function mapRoles(array $attributes): array
    {
        // ...
    }

    // + refreshUser() et supportsClass(), hérités de UserProviderInterface
}
```

`$attributes` contient les claims du token. {.opacity-60}

---
layout: default
class: sec-authn
---

# La ressource API Platform, elle, ne change pas

```php
// src/Entity/Photo.php
#[ApiResource(security: "is_granted('ROLE_USER')")]
#[GetCollection]
#[Post(security: "is_granted('ROLE_PHOTOS_WRITE')")]
class Photo
{
    // ...
}
```

<v-click>

#### Même expression qu'avec OAuth2. <br/> Seule la **source des rôles** a changé. {.mt-6}

</v-click>

---
layout: default
class: sec-authn
---

# Et si le client est lui aussi une app Symfony ?

<v-clicks>

- ✅ **Vérifier** un access token : natif (`access_token`)
- ❌ **Initier** le flow authorization_code : pas encore dans le core
- 📦 `drenso/symfony-oidc-bundle` couvre le login côté client

</v-clicks>

<v-click>

<Alert type="info">

🚧 Je travaille sur une série de **PRs pour amener ce flow dans Symfony directement**.

</Alert>

</v-click>
