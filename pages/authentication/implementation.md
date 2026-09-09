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

- 👉 Les **apps clientes** redirigent les utilisateurs vers l'**OIDC Provider**, qui les authentifie et **émet les tokens**
- 👥 Les **comptes** vivent dans l'OIDC Provider, plus dans votre base
- 🧩 Votre **API** ne fait que **vérifier** les tokens
- 🏦 Aucun écran de login ni de consentement à coder : elle se concentre sur le **métier**

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
- 🎯 Trois handlers natifs : **`oidc`**, **`oidc_user_info`** et **`oauth2`**

</v-clicks>

---
layout: default
class: sec-authn
---

# Trois façons de vérifier un token

<CardGrid :cols="3" class="handlers-grid">
  <Card v-click :accent="4" icon="🔏" title="<code>oidc</code>">
    <b>Offline</b><br/>
    Vérifie la <b>signature</b> du JWT avec les clés publiques du Provider, puis ses claims (<code>exp</code>, <code>aud</code>, <code>iss</code>).
  </Card>
  <Card v-click :accent="5" icon="🙋" title="<code>oidc_user_info</code>">
    <b>Online</b><br/>
    Présente le token à l'endpoint <b>userinfo</b> du Provider, qui le valide et renvoie les claims de l'utilisateur.
  </Card>
  <Card v-click :accent="6" icon="🔎" title="<code>oauth2</code>">
    <b>Online</b><br/>
    Présente le token à l'endpoint d'<b>introspection</b> du serveur d'autorisation, qui répond <code>active</code> et les claims.
  </Card>
</CardGrid>

<v-click>

<div class="slide-punch">Le handler change, pas le reste : même firewall, mêmes rôles, même <code>is_granted</code>.</div>

</v-click>

<style scoped>
.handlers-grid { margin-top: 1rem; align-items: stretch; }
.handlers-grid :deep(.ds-card__icon) { font-size: 2.7rem; }
</style>

---
layout: default
class: sec-authn
---

# Offline ou online : seul le token handler change

<div class="grid grid-cols-2 gap-6">

<div>

**Offline** : la signature suffit

```yaml
# security.firewalls.api.access_token
token_handler:
    oidc:
        algorithms: ['RS256']
        audience: 'cloudpics-api'
        issuers: ['https://id.cloudpics.example']
        discovery:
            base_uri: 'https://id.cloudpics.example/'
            cache: { id: cache.app }
```

<div class="slide-note">Clés publiques du Provider, découvertes via son <code>.well-known</code> : vérification <b>en local</b>.</div>

</div>

<div v-click>

**Online** : on interroge le Provider

```yaml
# security.firewalls.api.access_token
token_handler:
    oidc_user_info:
        base_uri: 'https://id.cloudpics.example/'
        claim: sub
        discovery:
            cache: { id: cache.app }
```

<div class="slide-note">Un appel HTTP au Provider à <b>chaque requête</b> entrante sur l'API.</div>

</div>

</div>

<style scoped>
.slide-note code { white-space: nowrap; }
</style>

---
layout: default
class: sec-authn
---

# Offline ou online : un arbitrage, pas un gagnant

|                            | `oidc` (offline)         | `oidc_user_info` (online) | `oauth2` (online)    |
|----------------------------|--------------------------|---------------------------|----------------------|
| **Appel réseau**           | Aucun                    | Un par requête            | Un par requête       |
| **Révocation d'un token**  | Visible à l'expiration   | Immédiate                 | Immédiate            |
| **Provider indisponible**  | L'API continue de servir | L'API ne répond plus      | L'API ne répond plus |
| **Validation de `aud`**    | Oui                      | Aucune                    | Oui                  |

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

# Scope, rôle, règle métier : trois questions

<CardGrid :cols="3" class="authz-grid">
  <Card v-click :accent="4" icon="🎫" title="Le scope">
    <i>«&nbsp;Qu'est-ce qu'Alice a autorisé PhotoPrint à faire&nbsp;?&nbsp;»</i><br/>
    Lire et ajouter : <code>photos:read photos:write</code>. Une app qui ne demande que la lecture n'écrira jamais.<br/>
    Dans le token : claim <code>scope</code>, standard OAuth2.
  </Card>
  <Card v-click :accent="5" icon="🧢" title="Le rôle">
    <i>«&nbsp;Qu'est-ce qu'Alice a le droit de faire chez CloudPics&nbsp;?&nbsp;»</i><br/>
    Bob, offre gratuite, ne peut pas écrire.<br/>
    Dans le token : claim du Provider (Keycloak : <code>realm_access.roles</code>).
  </Card>
  <Card v-click :accent="6" icon="🏠" title="La règle métier">
    <i>«&nbsp;Cette photo est-elle à Alice&nbsp;?&nbsp;»</i><br/>
    Le Provider n'en sait rien.<br/>
    Dans l'API, et nulle part ailleurs.
  </Card>
</CardGrid>

<v-click>

<div class="slide-punch">Le token répond aux deux premières questions.<br/>La troisième reste à <b>votre API</b>.</div>

</v-click>

<style scoped>
.authz-grid { margin-top: 1rem; align-items: stretch; }
.authz-grid :deep(.ds-card__icon) { font-size: 2.7rem; }
</style>

---
layout: default
class: sec-authn
---

# Dans la démo, tout finit dans `security`

```php
// src/Entity/Photo.php
#[ApiResource(security: "is_granted('ROLE_PHOTOS_READ')")]
#[GetCollection]
#[Get(security: "is_granted('ROLE_PHOTOS_READ') and object.owner == user.getUserIdentifier()")]
#[Post(security: "is_granted('ROLE_PHOTOS_WRITE')")]
class Photo
{
    // ...
}
```

<v-clicks>

- 🧢 `ROLE_PHOTOS_*` : les claims du token, mappés en rôles par un **UserProvider**
- 🏠 `object.owner == user` : la règle métier, connue de l'API seule
- 🎫 Symfony **8.2** : `OAUTH2_SCOPE(photos:read)` lit le scope, sans mapping

</v-clicks>

<v-click>

<div class="slide-punch">Le même <code>is_granted</code> qu'avec OAuth2 : la sécurité reste <b>déclarative</b>.</div>

</v-click>

---
layout: default
class: sec-authn
---

# Et si le client est lui aussi une app Symfony ?

**PhotoBook**, un autre service tiers, client **confidentiel** : il tourne sur son serveur et garde un `client_secret`.

<v-clicks>

- ✅ **Vérifier** un access token : natif (`access_token`)
- 🎉 **Initier** le flow : l'authenticator **`oidc_login`**, natif dans Symfony **8.2**
- 🛣️ **PKCE**, échange du code, **signature de l'ID token** vérifiée, logout, refresh

</v-clicks>

<div class="pr-row">
  <img v-click src="/pr-64954-og.png" alt="symfony/symfony PR 64954 : Add an OIDC Authorization Code Flow authenticator, par welcoMattic" />
  <div v-click>
    <div class="slide-punch">Mergé le 2 septembre, une dizaine de PR de suite depuis. <b>Livré en novembre 2026</b>.</div>
    <div class="slide-note">Pas de bundle dans la démo : <b>PhotoBook tourne déjà dessus</b>.</div>
  </div>
</div>

<style scoped>
.pr-row {
  margin-top: 0.8rem;
  display: grid;
  grid-template-columns: 0.85fr 1.15fr;
  gap: 1.4rem;
  align-items: center;
}
.pr-row img {
  width: 100%;
  border-radius: var(--radius-lg);
  border: 1px solid var(--c-border);
  box-shadow: var(--shadow-card);
}
</style>
