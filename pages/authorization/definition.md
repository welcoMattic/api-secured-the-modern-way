---
layout: statement
class: sec-authz
---

# OAuth2

**Déléguer l'accès** à ses ressources, <br> **sans partager ses identifiants**.

---
layout: statement
class: sec-authz
---

## 👀 {.!text-7xl .mb-6}

# Focus du jour

Le scénario **au nom de l'utilisateur** <br> (Authorization Code Flow).

<span class="text-base italic opacity-60">OAuth2 gère aussi les *Client Credentials Flow*, sans utilisateur. [RFC 6749](https://datatracker.ietf.org/doc/html/rfc6749)</span>

---
layout: default
class: sec-authz
---

# Alice, PhotoPrint et CloudPics

<div class="story">

<div class="beat" v-click>
  <div class="beat__label">Le décor</div>
  <div class="beat__body">
    👩‍🦰 Alice utilise <b>PhotoPrint</b> et <b>CloudPics</b>.<br/>
    📸 PhotoPrint veut <b>les photos d'Alice</b>, hébergées sur CloudPics.
  </div>
</div>

<div class="beat" v-click>
  <div class="beat__label">La contrainte</div>
  <div class="beat__body">
    🔐 Sans jamais lui demander son <b>mot de passe CloudPics</b>.
  </div>
</div>

<div class="beat" v-click>
  <div class="beat__label">Le flow</div>
  <div class="beat__body">
    🔗 PhotoPrint <b>redirige</b> Alice vers CloudPics, qui lui demande son accord.<br/>
    <blockquote class="mt-2 mb-4 !text-base">Autorises-tu PhotoPrint a accéder à tes photos CloudPics ?</blockquote>
    🔢 Alice <b>autorise</b> : CloudPics émet un <b>code</b> à usage unique.<br/>
    🔄 PhotoPrint <b>échange</b> ce code contre un <b>access token</b><br/>
    🌁 PhotoPrint <b>accède</b> aux photos d'Alice grâce à l'access token
  </div>
</div>

</div>

<v-click>

<Alert type="info">

**CloudPics** joue le rôle de serveur OAuth2, et **PhotoPrint** d'application cliente

</Alert>

</v-click>

<style scoped>
/* Récit en trois temps : le label porte le rythme, le corps porte l'histoire. */
.story {
  margin-top: 0.6rem;
  display: flex;
  flex-direction: column;
  gap: 1.15rem;
}
.beat {
  display: grid;
  grid-template-columns: 8.5rem 1fr;
  gap: 1.4rem;
  align-items: start;
}
.beat__label {
  padding-top: 0.35rem;
  font-family: "Sora", sans-serif;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--sec, var(--c-accent));
  border-top: 3px solid var(--sec, var(--c-accent));
}
.beat__body {
  font-size: 1.2rem;
  line-height: 1.7;
}
</style>

---
layout: iframe
url: https://s.icepanel.io/GPg85hHAHAN9PQ/prFj/landscape/diagrams/viewer?diagram=ArYURElFMf&drawer=collapsed&flow=MOk1HsWr2U&flow_step=qksr799o76&model=k2TvCLQDNF&x1=320.8&x2=2176.9&y1=-416&y2=576
---

---
layout: default
class: sec-authz
---

# Les parties prenantes

<CardGrid :cols="3" class="oauth-roles">
  <Card v-click :accent="1" icon="👩‍🦰" title="Alice">
    <b>Resource owner</b><br/>
    Elle seule peut accorder l'accès à ses photos.
  </Card>
  <Card v-click :accent="2" icon="🌁" title="PhotoPrint">
    <b>Client</b><br/>
    Demande les photos <b>pour le compte</b> d'Alice, avec son autorisation.
  </Card>
  <Card v-click :accent="3" icon="☁️" title="CloudPics">
    <b>Authorization server<br/>+ Resource server</b><br/>
    Émet les access tokens, et héberge les photos.
  </Card>
</CardGrid>

<style scoped>
.oauth-roles { margin-top: 1rem; align-items: stretch; }
.oauth-roles :deep(.ds-card__icon) { font-size: 2.7rem; }
</style>

---
layout: default
class: sec-authz
---

# La première faiblesse 

Le code d'autorisation passe en clair vers le client

<v-clicks>

- 🌐 **Web** : code dans l'**URL de redirection** → historique, logs, `Referer`
- 📱 **Mobile** : une app malveillante prend le **même URL scheme** (`photoprint://`) → capte le code

</v-clicks>

<v-click>

<div class="slide-punch">Le code d'autorisation peut être <b>volé</b>.</div>

</v-click>

---
layout: default
class: sec-authz
---

# Rien ne prouve que c'est PhotoPrint

<v-clicks>

- 📱 PhotoPrint tourne **chez Alice** (SPA, app mobile)
- 🔓 Un secret embarqué serait **extractible** (DevTools, décompilation)
- 💥 Un secret unique donnerait accès aux photos de TOUS les utilisateurs de CloudPics
- 🙅 À l'échange du code, PhotoPrint ne peut **rien prouver**

</v-clicks>

<v-click>

<div class="slide-punch">Le code volé s'échange <b>sans obstacle</b> → access token d'Alice.</div>

</v-click>

---
layout: default
class: sec-authz
---

# La parade : PKCE

**P**roof **K**ey for **C**ode **E**xchange ([RFC 7636](https://datatracker.ietf.org/doc/html/rfc7636))

<v-clicks>

- 🎲 **code_verifier** (secret) + empreinte SHA-256 **code_challenge**
- 🔗 Autorisation → envoie le **challenge** et l'algorithme utilisé (SHA-256)
- 🤝 Échange → envoie le **verifier** (requête directe, TLS)
- ✅ `SHA-256(verifier) == challenge` → token

</v-clicks>

<v-click>

<div class="slide-punch">Le challenge est <b>irréversible</b>, sans le verifier le code ne vaut rien.<br/>On prouve à l'échange qu'on est bien l'initiateur du flow.</div>

</v-click>

---
layout: default
class: sec-authz
---

# PKCE en séquence

```mermaid
%%{init: {"sequence": {"messageMargin": 16, "boxMargin": 4, "noteMargin": 4, "diagramMarginY": 0}}}%%
sequenceDiagram
    participant P as 🌁 PhotoPrint (client)
    participant A as 👩‍🦰 Alice (navigateur)
    participant C as ☁️ CloudPics (authZ server)

    Note over P: Génère code_verifier<br/>code_challenge = SHA-256(verifier)
    P->>A: Redirection vers CloudPics
    A->>C: Demande d'autorisation + code_challenge + algo
    Note over C: Alice s'authentifie et consent<br/>authorization code émis, lié au challenge
    C-->>A: Redirection retour + authorization code
    A-->>P: authorization code
    P->>C: authorization code + code_verifier (requête directe, TLS)
    Note over C: SHA-256(verifier) = challenge ?
    C-->>P: ✅ access token
```

<v-click>

<Alert type="info">

**OAuth 2.1** : PKCE obligatoire.

</Alert>

</v-click>

---
layout: default
class: sec-authz
---

# Le token ne prouve rien : le détenir suffit

```http
GET /api/photos HTTP/1.1
Host: api.cloudpics.example
Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...
```

<v-clicks>

- 🎫 **Bearer** = « au porteur » : aucune preuve n'est demandée au client
- 🕵️ Volé, il est **indiscernable** d'un token légitime. L'API ne peut pas trancher
- 🔒 D'où **TLS obligatoire**, et jamais dans une URL : historique, logs, `Referer`

</v-clicks>

<v-click>

<div class="slide-punch">Par défaut, rien n'empêche l'usage d'un token volé.<br/>On ne corrige pas ça, on <b>limite sa durée de validité</b>.</div>

</v-click>

<!--
Le mot à ne pas lâcher : « au porteur ». C'est un ticket de métro, pas une carte
d'identité. Le contrôleur vérifie le ticket, jamais qui le présente.

C'est exactement la faiblesse que PKCE corrigeait pour le code d'autorisation,
sauf qu'ici elle reste. D'où l'enchaînement sur les durées de vie et le refresh.

Si on demande comment faire mieux : DPoP (RFC 9449, Standards Track) lie le token
à une clé cryptographique du client, ce qui rend un token volé inutilisable.
Même idée que PKCE, appliquée au token. Ce n'est pas le comportement par défaut,
et Symfony ne le gère pas nativement.
-->
