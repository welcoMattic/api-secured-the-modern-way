---
layout: default
class: sec-authn
---

# OIDC adds an identity layer on top of OAuth2

<v-clicks>

- 🆔 **ID Token**: a JWT carrying the identity claims
- 📋 **Standard claims**: sub, aud, iss, exp...
- 🔗 **Discovery**: the `.well-known` endpoint
- 🔐 **UserInfo**: additional user data

</v-clicks>

---
layout: statement
class: sec-authn
---

# OIDC Provider

A **dedicated Provider** issues the tokens and serves the identities. <br/>
**Independently** of your API.

<span class="text-base italic opacity-60">In our story: CloudPics hands identity over to <b>CloudPics ID</b>. Its API only keeps the photos.</span>

---
layout: default
class: sec-authn
---

# The client gets the tokens, the API verifies them

```mermaid
sequenceDiagram
    participant C as 🌁 PhotoPrint (client)
    participant P as 🛂 CloudPics ID (OIDC Provider)
    participant A as ☁️ CloudPics API

    C->>P: redirect + authorization request (PKCE)
    Note over P: Alice authenticates (SSO)
    P-->>C: authorization code,<br/>exchanged for id_token + access_token
    C->>A: GET /api/photos + Bearer access_token
    A<<-->>P: verifies the access token
    A-->>C: 200 OK
```

---
layout: default
class: sec-authn
---

# The stakeholders

<CardGrid :cols="3" class="oidc-roles">
  <Card v-click :accent="4" icon="👩‍🦰" title="Alice">
    <b>End-User</b><br/>
    A human user
  </Card>
  <Card v-click :accent="5" icon="🌁" title="PhotoPrint">
    <b>Relying Party (RP)</b><br/>
    The client asking for the authentication and the claims.
  </Card>
  <Card v-click :accent="6" icon="🛂" title="CloudPics ID">
    <b>OpenID Provider (OP)</b><br/>
    Authenticates Alice, then serves the claims to the RP.
  </Card>
</CardGrid>

<v-click>

<div class="oidc-outsider">
  <span class="oidc-outsider__icon">🧩</span>
  <span>
    And <b>CloudPics API</b>, your API Platform app?<br/> A <b>Resource Server</b>: an OAuth2 name, left unchanged by the OIDC spec.
  </span>
</div>

</v-click>

<style scoped>
.oidc-roles { margin-top: 1rem; align-items: stretch; }
.oidc-roles :deep(.ds-card__icon) { font-size: 2.7rem; }
/* L'API est volontairement hors du bloc : elle n'est pas un rôle OIDC. */
.oidc-outsider {
  margin-top: 1.6rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.9rem 1.3rem;
  border: 2px dashed var(--c-border-strong);
  border-radius: var(--radius-lg);
  font-size: 1.1rem;
  color: var(--c-muted);
}
.oidc-outsider b { color: var(--c-fg); }
.oidc-outsider__icon { font-size: 1.9rem; line-height: 1; }
</style>

---
layout: default
class: sec-authn
---

# Two tokens, two recipients

<CardGrid :cols="2" class="mt-8">
  <Card v-click :accent="4" icon="🪪" title="ID token">
    For the <b>client application</b>.<br/>
    Who the user is, and how they authenticated. <br/>
    > An ID card
  </Card>
  <Card v-click :accent="6" icon="🎫" title="Access token">
    For the <b>API</b>.<br/>
    What the bearer is allowed to do. <br/>
    > A concert ticket
  </Card>
</CardGrid>

<v-click>

<Alert type="warning">

The ID token is **not** a key to the API. Only the **access token** is.

</Alert>

</v-click>


