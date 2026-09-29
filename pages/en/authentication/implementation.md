---
layout: fact
class: sec-authn
---

## We are not implementing our own OIDC Provider.
<div class="slide-punch is-centered">It is a full-time job of its own.</div>

---
layout: default
class: sec-authn
---

# Open source or SaaS: they all speak OIDC

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

<ServiceGroup label="Both" :cols="1">
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

<div class="slide-note is-centered">🇪🇺 European origin. In the middle, those that exist <b>in both models</b>.</div>

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

# Need social SSO?

A few client credentials to configure, and it is plugged in.

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

<div class="slide-note is-centered">Not a single line of code, <b>configuration only</b>. Keycloak ships twelve connectors out of the box.</div>

<style scoped>
.sso-grid { margin-top: 0.2rem; }
</style>

---
layout: default
class: sec-authn
---

# Enterprise SSO still speaks SAML

<div class="bridge">
  <div class="bridge__node">
    <b>PhotoBook</b>
    <small>your Symfony app</small>
  </div>
  <div class="bridge__hop"><span>OIDC</span></div>
  <div class="bridge__node bridge__node--pivot">
    <b>CloudPics ID</b>
    <small>OpenID Provider<br/>+ SAML Service Provider</small>
  </div>
  <div class="bridge__hop"><span>SAML</span></div>
  <div class="bridge__node">
    <b>Corporate IdP</b>
    <small>ADFS, Shibboleth, Entra ID</small>
  </div>
</div>

<v-clicks>

- 🏢 Your enterprise customers **already have** a SAML IdP, and they are not going to replace it
- 🔌 Keycloak speaks SAML **natively**: the corporate IdP is added as one more brokered provider
- 🎭 Toward that IdP, your Provider is the **Service Provider**, the SAML word for a client app
- 🙈 Your Symfony app **never sees** an assertion: it keeps receiving OIDC tokens

</v-clicks>

<v-click>

<div class="slide-punch">Same authenticator, same tokens, same <code>is_granted</code>.<br/>The Provider does the <b>translation</b>.</div>

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

# Your API goes back to being a plain resource server

<v-clicks>

- 👉 **Client apps** redirect users to the **OIDC Provider**, which authenticates them and **issues the tokens**
- 👥 **Accounts** live in the OIDC Provider, no longer in your database
- 🧩 Your **API** only **verifies** the tokens
- 🏦 No login or consent screen to write: it focuses on the **business logic**

</v-clicks>

---
layout: default
class: sec-authn
---

# Symfony verifies access tokens natively

<v-clicks>

- 🔌 The **`access_token`** authenticator in the firewall
- 📥 Reads the **`Authorization: Bearer`** header by default
- 🧩 A **token handler** decides *how* to validate
- 🎯 Three built-in handlers: **`oidc`**, **`oidc_user_info`** and **`oauth2`**

</v-clicks>

---
layout: default
class: sec-authn
---

# Three ways to verify a token

<CardGrid :cols="3" class="handlers-grid">
  <Card v-click :accent="4" icon="🔏" title="<code>oidc</code>">
    <b>Offline</b><br/>
    Checks the JWT <b>signature</b> against the Provider's public keys, then its claims (<code>exp</code>, <code>aud</code>, <code>iss</code>).
  </Card>
  <Card v-click :accent="5" icon="🙋" title="<code>oidc_user_info</code>">
    <b>Online</b><br/>
    Presents the token to the Provider's <b>userinfo</b> endpoint, which validates it and returns the user's claims.
  </Card>
  <Card v-click :accent="6" icon="🔎" title="<code>oauth2</code>">
    <b>Online</b><br/>
    Presents the token to the authorization server's <b>introspection</b> endpoint, which answers <code>active</code> plus the claims.
  </Card>
</CardGrid>

<v-click>

<div class="slide-punch">The handler changes, nothing else does: same firewall, same roles, same <code>is_granted</code>.</div>

</v-click>

<style scoped>
.handlers-grid { margin-top: 1rem; align-items: stretch; }
.handlers-grid :deep(.ds-card__icon) { font-size: 2.7rem; }
</style>

---
layout: default
class: sec-authn
---

# Offline or online: a trade-off, not a winner

|                           | `oidc` (offline)        | `oidc_user_info` (online) | `oauth2` (online)     |
|---------------------------|-------------------------|---------------------------|-----------------------|
| **Network call**          | None                    | One per request           | One per request       |
| **Token revocation**      | Visible at expiry       | Immediate                 | Immediate             |
| **Provider unavailable**  | The API keeps serving   | The API stops answering   | The API stops answering |
| **`aud` validation**      | Yes                     | None                      | Yes                   |

<v-click>

<div class="slide-punch">Short access tokens + offline: the best trade-off in most cases.</div>

</v-click>

---
layout: default
class: sec-authn
---

# In the demo, it all ends up in `security`

```php
// src/Entity/Photo.php
#[ApiResource(security: "is_granted('ROLE_PHOTOS_READ')")]
#[GetCollection]
#[Get(security: "is_granted('ROLE_PHOTOS_READ') and object.owner == user.getUserIdentifier()")]
#[Post(security: "is_granted('ROLE_PHOTOS_WRITE')")]
class Photo { /* ... */ }
```

<v-clicks>

- 🧢 Provider roles ≠ Symfony roles: `OidcUser` only comes with `ROLE_USER`
- 🔁 Mapping them to `ROLE_PHOTOS_*`: a hand-written **UserProvider**, **mandatory**
- 🏠 `object.owner == user`: the business rule, known to the API alone
- 🎫 Symfony **8.2**: `OAUTH2_SCOPE(photos:read)` reads the scope, no mapping needed

</v-clicks>

<v-click>

<div class="slide-punch">An access token states what its bearer <b>may do</b>. The API enforces it right here, <b>declaratively</b>.</div>

</v-click>

---
layout: default
class: sec-authn
---

# What if the client is a Symfony app too?

**PhotoBook**, another third-party service, a **confidential** client: it runs on its own server and keeps a `client_secret`.

<v-clicks>

- ✅ **Verifying** an access token: built in (`access_token`)
- 🎉 **Starting** the flow: the **`oidc_login`** authenticator, built into Symfony **8.2**
- 🛣️ **PKCE**, code exchange, **ID token signature** verified, logout, refresh

</v-clicks>

<div class="pr-row">
  <img v-click src="/pr-64954-og.png" alt="symfony/symfony PR 64954: Add an OIDC Authorization Code Flow authenticator, by welcoMattic" />
  <div v-click>
    <div class="slide-punch">Merged on September 2nd, with about a dozen follow-up PRs since. <b>Shipping in November 2026</b>.</div>
    <div class="slide-note">No bundle in the demo: <b>PhotoBook already runs on it</b>.</div>
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
