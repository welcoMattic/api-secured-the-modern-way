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

# 👀 Focus du jour

Le scénario **au nom de l'utilisateur** (authorization code).

<span class="text-base italic opacity-60">OAuth2 gère aussi les *client credentials*, sans utilisateur. [RFC 6749](https://datatracker.ietf.org/doc/html/rfc6749)</span>

---
layout: default
class: sec-authz
---

# Alice, PhotoPrint et CloudPics

<v-clicks>

- 👩‍🦰 Alice utilise **PhotoPrint** et **CloudPics**
- 📸 PhotoPrint veut **les photos d'Alice** sur CloudPics
- 🔐 Sans le **mot de passe CloudPics** d'Alice
- 🔗 PhotoPrint **redirige** Alice vers CloudPics
- 🔢 Alice **autorise** → un **code** à usage unique
- 🔄 Code **échangé** contre un **access token**
- ✅ Access token → **accès aux photos**

</v-clicks>

<v-click>

<Alert type="info">

**CloudPics** joue le rôle de serveur OAuth2, et **PhotoPrint** d'application cliente

</Alert>

</v-click>

---
layout: iframe
url: https://s.icepanel.io/GPg85hHAHAN9PQ/prFj
---

---
layout: default
class: sec-authz
---

# Les rôles OAuth2

<div class="grid grid-cols-3 gap-6 mt-6 w-full">

<div v-click class="p-6 rounded-xl bg-sky-50 border-2 border-sky-200 text-center">
  <div class="text-5xl mb-3">☁️</div>
  <div class="font-bold text-xl text-sky-900">CloudPics</div>
  <div class="text-sm text-gray-600 mt-2">Resource server <br/> + Authorization server</div>
</div>

<div v-click class="p-6 rounded-xl bg-fuchsia-50 border-2 border-fuchsia-200 text-center">
  <div class="text-5xl mb-3">🌁</div>
  <div class="font-bold text-xl text-fuchsia-900">PhotoPrint</div>
  <div class="text-sm text-gray-600 mt-2">Client application</div>
</div>

<div v-click class="p-6 rounded-xl bg-amber-50 border-2 border-amber-200 text-center">
  <div class="text-5xl mb-3">👩‍🦰</div>
  <div class="font-bold text-xl text-amber-900">Alice</div>
  <div class="text-sm text-gray-600 mt-2">Resource owner</div>
</div>

</div>

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

#### Le code d'autorisation peut être **volé**. {.mt-8}

</v-click>

---
layout: default
class: sec-authz
---

# Rien ne prouve que c'est PhotoPrint

<v-clicks>

- 📱 PhotoPrint tourne **chez Alice** (SPA, app mobile)
- 🔓 Un secret embarqué serait **extractible** (DevTools, décompilation)
- 🙅 À l'échange du code, PhotoPrint ne peut **rien prouver**

</v-clicks>

<v-click>

#### Le code volé s'échange **sans obstacle** → access token d'Alice. {.mt-8}

</v-click>

---
layout: default
class: sec-authz
---

# La parade : PKCE

**P**roof **K**ey for **C**ode **E**xchange ([RFC 7636](https://datatracker.ietf.org/doc/html/rfc7636)) {.text-xl .opacity-60}

<v-clicks>

- 🎲 **code_verifier** (secret) + empreinte SHA-256 **code_challenge**
- 🔗 Autorisation → envoie le **challenge** et l'algorithme utilisé (SHA-256)
- 🤝 Échange → envoie le **verifier** (requête directe, TLS)
- ✅ `SHA-256(verifier) == challenge` → token

</v-clicks>

<v-click>

#### Le challenge est **irréversible**. Sans le verifier, le code ne vaut rien. {.mt-6}

#### On prouve lors de l'échange qu'on est bien l'initiateur du flow. {.mt-6}

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

**OAuth 2.1** : PKCE obligatoire. Le bundle League l'exige déjà (clients publics).

</Alert>

</v-click>

---
layout: default
class: sec-authz
---

# Le token en transit

```http
GET /api/photos HTTP/1.1
Host: api.cloudpics.example
Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...
```

<v-clicks>

- 🎫 **Bearer** = porteur : le détenir suffit
- 🔒 **HTTPS** obligatoire, jamais dans l'URL
- ⏳ Compensé par des **durées de vie courtes**

</v-clicks>
