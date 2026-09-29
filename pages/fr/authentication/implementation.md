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

# Open source ou SaaS : tous parlent OIDC

<div class="providers">

<ServiceGroup label="Open source" :cols="3">
  <Logo :size="1.9" eu src="/authelia.png" label="Authelia" />
  <Logo :size="1.9" eu src="/goauthentik.png" label="Authentik" />
  <Logo :size="1.9" eu src="/ferriskey.png" label="FerrisKey" />
  <Logo :size="1.9" eu src="/ory-hydra.png" label="Ory Hydra" />
  <Logo :size="1.9" eu src="/pocketid.png" label="PocketID" />
  <Logo :size="1.9" eu src="/rauthy.png" label="Rauthy" />
  <Logo :size="1.9" src="/casdoor.png" label="Casdoor" />
  <Logo :size="1.9" src="/dex.svg" label="Dex" />
  <Logo :size="1.9" src="/keycloak.png" label="Keycloak" />
</ServiceGroup>

<ServiceGroup label="Les deux" :cols="1">
  <Logo :size="1.9" eu src="/gravitee.webp" label="Gravitee AM" />
  <Logo :size="1.9" eu src="/zitadel.png" label="Zitadel" />
  <Logo :size="1.9" src="/supertokens.png" label="SuperTokens" />
</ServiceGroup>

<ServiceGroup label="SaaS" :cols="4">
  <Logo :size="1.9" eu src="/cidaas.png" label="Cidaas" />
  <Logo :size="1.9" eu src="/cloud-iam.png" label="Cloud-IAM" />
  <Logo :size="1.9" eu src="/please-open-it.svg" badge="/clevercloud.svg" badgeAlt="Clever Cloud" label="Please Open It" />
  <Logo :size="1.9" src="/auth0.png" label="Auth0" />
  <Logo :size="1.9" src="/aws-cognito.png" label="AWS Cognito" />
  <Logo :size="1.9" src="/clerk.png" label="Clerk" />
  <Logo :size="1.9" src="/entra_id.png" label="Microsoft Entra ID" />
  <Logo :size="1.9" src="/firebase.png" label="Firebase Auth" />
  <Logo :size="1.9" src="/kinde.png" label="Kinde" />
  <Logo :size="1.9" src="/loginradius.png" label="LoginRadius" />
  <Logo :size="1.9" src="/okta.png" label="Okta" />
  <Logo :size="1.9" src="/pingone.svg" label="PingOne" />
</ServiceGroup>

</div>

<div class="slide-note is-centered">🇪🇺 origine européenne. Au milieu, ceux qui existent <b>dans les deux modèles</b>.</div>

<style scoped>
/* Deux familles et leur intersection, côte à côte : la colonne du milieu montre que
   la frontière open source / SaaS n'est pas une frontière de protocole. */
.providers {
  margin-top: 0.6rem;
  display: grid;
  grid-template-columns: 3fr 1.3fr 4fr;
  gap: 0.8rem;
  align-items: stretch;
}
.providers :deep(.ds-sgroup) { border-color: var(--c-border); }
.providers :deep(.ds-sgroup:nth-child(2)) {
  background: rgba(var(--a-4-rgb), 0.07);
  border-color: rgba(var(--a-4-rgb), 0.3);
}
.providers :deep(.ds-logo__label) { font-size: 0.8rem; }
</style>

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

# Le SSO d'entreprise parle encore SAML

<div class="bridge">
  <div class="bridge__node">
    <b>PhotoBook</b>
    <small>votre app Symfony</small>
  </div>
  <div class="bridge__hop"><span>OIDC</span></div>
  <div class="bridge__node bridge__node--pivot">
    <b>CloudPics ID</b>
    <small>OpenID Provider<br/>+ Service Provider SAML</small>
  </div>
  <div class="bridge__hop"><span>SAML</span></div>
  <div class="bridge__node">
    <b>IdP d'entreprise</b>
    <small>ADFS, Shibboleth, Entra ID</small>
  </div>
</div>

<v-clicks>

- 🏢 Vos clients grands comptes **ont déjà** un IdP SAML, et ils n'en changeront pas
- 🔌 Keycloak parle SAML **nativement** : l'IdP d'entreprise devient un provider brokerisé de plus
- 🎭 Face à cet IdP, votre Provider est le **Service Provider**, le mot SAML pour une app cliente
- 🙈 Votre app Symfony **ne voit jamais** d'assertion : elle continue de recevoir des tokens OIDC

</v-clicks>

<v-click>

<div class="slide-punch">Même authenticator, mêmes tokens, même <code>is_granted</code>.<br/>C'est le Provider qui fait la <b>traduction</b>.</div>

</v-click>

<style scoped>
/* Le pont se lit de gauche à droite : deux protocoles, et au milieu le seul
   acteur qui connaît les deux. Les flèches portent le nom du protocole, donc la
   salle voit tout de suite où SAML s'arrête. */
.bridge {
  margin-top: 0.7rem;
  display: grid;
  grid-template-columns: 1fr auto 1.2fr auto 1fr;
  align-items: center;
  gap: 0.5rem;
}
.bridge__node {
  padding: 0.6rem 0.9rem;
  border: 2px solid var(--c-border-strong);
  border-radius: 0.7rem;
  text-align: center;
  line-height: 1.25;
}
.bridge__node b {
  font-family: "Sora", var(--font-emoji), sans-serif;
  font-size: 1rem;
}
.bridge__node small {
  display: block;
  margin-top: 0.15rem;
  font-size: 0.74rem;
  color: var(--c-muted);
}
/* Le pivot est le seul à porter la couleur de section : c'est lui qui fait le pont. */
.bridge__node--pivot {
  border-color: var(--sec);
  background: rgba(var(--a-4-rgb), 0.08);
}
/* Le libellé du protocole tient au dessus d'un trait fléché tracé en CSS. */
.bridge__hop {
  position: relative;
  width: 5.5rem;
  padding-bottom: 0.55rem;
  text-align: center;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.1em;
  color: var(--sec);
}
.bridge__hop::before {
  content: '';
  position: absolute;
  left: 0;
  right: 0.4rem;
  bottom: 0.25rem;
  border-top: 2px solid var(--c-fg);
}
.bridge__hop::after {
  content: '';
  position: absolute;
  right: 0;
  bottom: 0.25rem;
  transform: translateY(50%);
  border: 5px solid transparent;
  border-right: 0;
  border-left: 9px solid var(--c-fg);
}
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
class Photo { /* ... */ }
```

<v-clicks>

- 🧢 Rôles du Provider ≠ rôles Symfony : `OidcUser` n'arrive qu'avec `ROLE_USER`
- 🔁 Les mapper vers `ROLE_PHOTOS_*` : un **UserProvider** écrit à la main, **obligatoire**
- 🏠 `object.owner == user` : la règle métier, connue de l'API seule
- 🎫 Symfony **8.2** : `OAUTH2_SCOPE(photos:read)` lit le scope, sans mapping

</v-clicks>

<v-click>

<div class="slide-punch">Un access token dit ce que son porteur <b>peut faire</b>. L'API l'applique ici, en <b>déclaratif</b>.</div>

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
