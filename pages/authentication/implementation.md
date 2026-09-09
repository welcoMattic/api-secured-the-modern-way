---
layout: fact
class: sec-authn
---

## Nous n'implémentons pas notre propre OIDC Provider.
<div class="slide-punch is-centered">C'est un métier à part entière.</div>

---
layout: default
class: sec-authn
---

# OIDC Providers (OP) Open Source

<ServiceGroup europe label="Origine européenne" :cols="4" class="mt-3">
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

<ServiceGroup europe label="Origine européenne" :cols="5" class="mt-3">
  <Logo :size="2.4" src="/cidaas.png" label="Cidaas" />
  <Logo :size="2.4" src="/cloud-iam.png" label="Cloud-IAM" />
  <Logo :size="2.4" src="/gravitee.webp" label="Gravitee AM" />
  <Logo :size="2.4" src="/please-open-it.svg" badge="/clevercloud.svg" badgeAlt="Clever Cloud" label="Please Open It <br/> <small>(le Keycloak managé de Clever Cloud)</small>" />
  <Logo :size="2.4" src="/zitadel.png" label="Zitadel" />
</ServiceGroup>

<ServiceGroup label="Reste du monde" :cols="5" class="mt-3">
  <Logo :size="2.4" src="/auth0.png" label="Auth0" />
  <Logo :size="2.4" src="/aws-cognito.png" label="AWS Cognito" />
  <Logo :size="2.4" src="/clerk.png" label="Clerk" />
  <Logo :size="2.4" src="/entra_id.png" label="Microsoft Entra ID <br/> <small>(ex-Azure AD)</small>" />
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

# Besoin de SSO social ?

Quelques client credentials à configurer, et c'est branché.

<LogoGrid :cols="4" :gapY="2.4" class="sso-grid">
  <Logo :size="4.2" src="/google.svg" label="Google" />
  <Logo :size="4.2" src="/microsoft.svg" label="Microsoft" />
  <Logo :size="4.2" src="/paypal.svg" label="PayPal" />
  <Logo :size="4.2" src="/facebook.svg" label="Facebook" />
  <Logo :size="4.2" src="/github.png" label="GitHub" />
  <Logo :size="4.2" src="/gitlab.svg" label="GitLab" />
  <Logo :size="4.2" src="/linkedin.svg" label="LinkedIn" />
  <Logo :size="4.2" src="/bitbucket.svg" label="Bitbucket" />
</LogoGrid>

<div class="slide-note is-centered">Pas une ligne de code, <b>que de la configuration</b>. Keycloak fournit douze connecteurs en standard.</div>

<style scoped>
.sso-grid { margin-top: 0.2rem; }
</style>

---
layout: default
class: sec-authn
---

# Votre API redevient un simple resource server

<v-clicks>

- 👉 Les **apps clientes** redirigent les utilisateurs vers l'**OIDC Provider** 
- 🛂 L'**OIDC Provider** les authentifie et **émet les tokens**
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
- 🎯 Deux handlers OIDC natifs : **`oidc`** et **`oidc_user_info`**. Et `oauth2` pour l'introspection (RFC 7662)

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
                        audience: 'cloudpics-api'
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

<div class="slide-note">Symfony 8.2 : <code>RS256</code> par défaut, <code>enforce_at_jwt_type: true</code> exige le <code>typ: at+jwt</code> (RFC 9068).</div>

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
                        claim: sub
                        discovery:
                            cache: { id: cache.app }
```

<v-click>

<Alert type="info">

`composer require symfony/http-client`. <br/> Un appel HTTP au Provider à **chaque requête** entrante sur l'API.

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
| **Validation de `aud`**  | Oui                       | **Aucune**                |
| **Claims lus**           | Ceux du token             | Ceux de `userinfo`        |
| **Dépendance**           | `web-token/jwt-library`   | `symfony/http-client`     |

<v-click>

<div class="slide-punch">Access tokens courts + offline : le meilleur compromis dans la majorité des cas.</div>

</v-click>

---
layout: statement
class: sec-authn
---

# Authentifié n'est pas autorisé

Le token dit **qui** appelle. <br> Reste à décider **ce qu'il peut faire** : retour à l'autorisation.

---
layout: default
class: sec-authn
---

# OIDC ne standardise pas la notion de rôle

<v-clicks>

- 🪪 `OidcUser` par défaut : **`ROLE_USER`**, et rien d'autre
- 🧾 RFC 9068 recommande `roles`, `groups`, `entitlements`. Keycloak émet `realm_access.roles`
- 🔁 À vous de **mapper** les claims vers des rôles Symfony

</v-clicks>

<v-click>

<Alert type="warning">

Le code de Symfony le dit explicitement : les specs OIDC et OAuth n'ont **aucune** notion de rôle.

</Alert>

</v-click>

<div class="slide-note">Les scopes <code>PHOTOS_READ</code> / <code>PHOTOS_WRITE</code> de la section OAuth2 deviennent ici des <b>rôles</b> realm, portés par les scopes <code>photos:read</code> / <code>photos:write</code>.</div>

---
layout: default
class: sec-authn
---

# Deux claims, et une intersection

<v-clicks>

- 🧾 Les **rôles du compte** d'Alice, chez CloudPics ID : ce qu'elle a le droit de faire
- 🎫 `scope` : ce qu'Alice a autorisé **cette app** à faire en son nom
- 🤝 `realm_access.roles` du token : l'**intersection** des deux, rien de plus

</v-clicks>

<v-click>

<div class="slide-punch">C'est le Provider qui croise, pas votre API.<br/>Keycloak : <i>full scope allowed</i> off + role scope mapping. Auth0 : RBAC.</div>

</v-click>

<div class="slide-note">Bob, offre gratuite en lecture seule, demande <code>photos:write</code> comme Alice : CloudPics ID ne le lui accorde ni en scope, ni en rôle. Le resource server, lui, n'a plus qu'un claim à lire.</div>

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

<div class="slide-note"><code>$attributes</code> contient les claims du token.</div>

---
layout: default
class: sec-authn
---

# La ressource API Platform ne change pas

```php
// src/Entity/Photo.php
#[ApiResource(security: "is_granted('ROLE_PHOTOS_READ')")]
#[GetCollection]
#[Post(security: "is_granted('ROLE_PHOTOS_WRITE')")]
class Photo
{
    // ...
}
```

<v-click>

<div class="slide-punch">Le même <code>is_granted</code> qu'avec OAuth2, au préfixe près.<br/>Seule la <b>source des rôles</b> a changé.</div>

</v-click>

---
layout: default
class: sec-authn
---

# Symfony 8.2 lit le claim `scope` nativement

```php
// src/Entity/Photo.php
#[ApiResource(security: "is_granted('OAUTH2_SCOPE(photos:read)')")]
#[GetCollection]
#[Post(security: "is_granted('OAUTH2_SCOPE(photos:write)')")]
class Photo
{
    // ...
}
```

<v-clicks>

- 🎫 L'authenticator `access_token` pose les scopes du token dans l'attribut `oauth2_scope` (claim `scope`, ou `scp`)
- 🗳️ `OAUTH2_SCOPE(a b)` exige **tous** les scopes listés
- 🚫 Refus : `403` avec le challenge `insufficient_scope` de la RFC 6750 §3.1 dans `WWW-Authenticate`

</v-clicks>

<v-click>

<div class="slide-punch">Le claim <code>scope</code> porte déjà l'intersection : rien à mapper.<br/>Les rôles restent pour ce qu'un scope ne dit pas.</div>

</v-click>

---
layout: default
class: sec-authn
---

# Et si le client est lui aussi une app Symfony ?

**PhotoBook**, un autre service tiers qui veut les photos d'Alice. Client **confidentiel** : il tourne sur son serveur et peut garder un `client_secret`.

<v-clicks>

- ✅ **Vérifier** un access token : natif (`access_token`)
- ✅ **Initier** le flow authorization_code : natif à partir de Symfony **8.2**
- 🎉 Un firewall `oidc_login`, mergé dans le Core début septembre

  - 🛣️ Redirection vers l'OIDC Provider, **PKCE S256** par défaut
  - 🔄 Échange du code contre les tokens, client authentifié (`client_secret_basic`)
  - 🔏 **Signature de l'ID token** vérifiée contre le JWKS du Provider
  - 🪪 Authentification de l'utilisateur au sein de l'app Symfony
  - 🚪 **RP-Initiated Logout**, renouvellement par **refresh token**

</v-clicks>

---
layout: default
class: sec-authn
---

# C'est natif dans Symfony 8.2 !

PR mergée dans **8.2** le 2 septembre, une dizaine de PR de suite depuis. Livrée en novembre 2026.

<div class="pr-shot">
  <img src="/pr-64954.png" alt="symfony/symfony PR 64954 : Add an OIDC Authorization Code Flow authenticator" />
</div>

<div class="slide-note is-centered pr-link">github.com/symfony/symfony/pull/<b>64954</b></div>

<div class="slide-punch">Pas de bundle dans la démo : <b>PhotoBook tourne déjà dessus</b>.</div>

<style scoped>
.pr-shot {
  margin-top: 0.4rem;
  display: flex;
  justify-content: center;
}
.pr-shot img {
  width: 100%;
  max-width: 32rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--c-border);
  box-shadow: var(--shadow-card);
}
.pr-link { font-family: "Fira Code", monospace; }
</style>
