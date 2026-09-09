---
layout: default
class: sec-authn
---

# OIDC ajoute une couche d'identité à OAuth2

<v-clicks>

- 🆔 **ID Token** : JWT avec les claims d'identité
- 📋 **Claims standards** : sub, aud, iss, exp...
- 🔗 **Discovery** : endpoint `.well-known`
- 🔐 **UserInfo** : données utilisateur supplémentaires

</v-clicks>

---
layout: statement
class: sec-authn
---

# OIDC Provider

Un **Provider dédié** émet les tokens et fournit les identités. <br/>
**Indépendamment** de votre API.

<span class="text-base italic opacity-60">Dans notre histoire : CloudPics confie l'identité à <b>CloudPics ID</b>. Son API ne garde que les photos.</span>

---
layout: default
class: sec-authn
---

# Le client obtient les tokens, l'API les vérifie

```mermaid
sequenceDiagram
    participant C as 🌁 PhotoPrint (client)
    participant P as 🛂 CloudPics ID (OIDC Provider)
    participant A as ☁️ CloudPics API

    C->>P: redirection + demande d'autorisation (PKCE)
    Note over P: Alice s'authentifie (SSO)
    P-->>C: authorization code,<br/>échangé contre id_token + access_token
    C->>A: GET /api/photos + Bearer access_token
    A<<-->>P: vérifie l'access token
    A-->>C: 200 OK
```

---
layout: default
class: sec-authn
---

# Les parties prenantes

<CardGrid :cols="3" class="oidc-roles">
  <Card v-click :accent="4" icon="👩‍🦰" title="Alice">
    <b>End-User</b><br/>
    Utilisateur humain
  </Card>
  <Card v-click :accent="5" icon="🌁" title="PhotoPrint">
    <b>Relying Party (RP)</b><br/>
    Le client qui réclame l'authentification et les claims.
  </Card>
  <Card v-click :accent="6" icon="🛂" title="CloudPics ID">
    <b>OpenID Provider (OP)</b><br/>
    Authentifie Alice, puis fournit les claims au RP.
  </Card>
</CardGrid>

<v-click>

<div class="oidc-outsider">
  <span class="oidc-outsider__icon">🧩</span>
  <span>
    Et <b>CloudPics API</b>, votre API Platform ?<br/> Un <b>Resource Server</b> : une dénomination OAuth2, que la spec OIDC ne change pas.
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

# Deux tokens, deux destinataires

<CardGrid :cols="2" class="mt-8">
  <Card v-click :accent="4" icon="🪪" title="ID token">
    Pour l'<b>application cliente</b>.<br/>
    Qui est l'utilisateur, et comment il s'est authentifié. <br/>
    > Carte d'identité
  </Card>
  <Card v-click :accent="6" icon="🎫" title="Access token">
    Pour l'<b>API</b>.<br/>
    Ce que le porteur a le droit de faire. <br/>
    > Billet de concert
  </Card>
</CardGrid>

<v-click>

<Alert type="warning">

L'ID token n'est **pas** une clé d'accès à l'API. Seul l'**access token** l'est.

</Alert>

</v-click>

---
layout: default
class: sec-authn
---

# Les JW* expliqués {class="!mb-4"}

| Acronyme | Nom complet                      | Rôle                                                          | Métaphore                          |
|----------|----------------------------------|---------------------------------------------------------------|------------------------------------|
| **JWT**  | **J**SON **W**eb **T**oken       | La structure des claims, toujours emballée en JWS ou JWE      | Le courrier                        |
| **JWS**  | **J**SON **W**eb **S**ignature   | Intégrité et authenticité : un token **signé**                | Une enveloppe transparente scellée |
| **JWE**  | **J**SON **W**eb **E**ncryption  | Confidentialité : un token **chiffré**                        | Une boîte opaque et verrouillée    |
| **JWKS** | **J**SON **W**eb **K**ey **S**et | Les clés publiques du Provider, pour vérifier les signatures  | Le trousseau                       |
