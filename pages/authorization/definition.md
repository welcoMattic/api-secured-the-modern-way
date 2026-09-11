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

<span class="text-base italic opacity-60">Sans utilisateur dans la boucle, une app qui appelle une API pour son propre compte : *Client Credentials Flow*, hors focus.</span>

---
layout: default
class: sec-authz
---

# Alice, PhotoPrint et CloudPics

<div class="story">

<div class="beat" v-click>
  <div class="beat__label">Le décor</div>
  <div class="beat__body">
    👩‍🦰 Alice héberge ses photos sur <b>CloudPics</b>.<br/>
    📸 Depuis <b>PhotoPrint</b>, une app web tierce, elle veut <b>faire imprimer ses photos CloudPics</b>.
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
    <blockquote class="mt-2 mb-4 !text-base">Autorises-tu PhotoPrint à accéder à tes photos CloudPics ?</blockquote>
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
layout: default
class: sec-authz
---

# Le flow, étape par étape

<div class="seq">
  <div class="seq__actors">
    <div class="seq__actor">🌁 PhotoPrint <small>client</small></div>
    <div class="seq__actor">👩‍🦰 Alice <small>navigateur</small></div>
    <div class="seq__actor">☁️ CloudPics <small>serveur OAuth2</small></div>
  </div>
  <div class="seq__body">
    <div class="seq__life" style="--lane: 0"></div>
    <div class="seq__life" style="--lane: 1"></div>
    <div class="seq__life" style="--lane: 2"></div>
    <div v-click="1" class="seq__msg seq__msg--back" style="--row: 0; --left: 0; --span: 1">« Imprime mes photos CloudPics »</div>
    <div v-click="1" class="seq__msg" style="--row: 1; --left: 0; --span: 1">Redirection vers CloudPics</div>
    <div v-click="2" class="seq__msg" style="--row: 2; --left: 1; --span: 1">Demande d'autorisation</div>
    <div v-click="2" class="seq__note" style="--row: 3; --lane: 2">Alice s'authentifie et consent</div>
    <div v-click="3" class="seq__msg seq__msg--back seq__msg--dashed" style="--row: 4; --left: 1; --span: 1">Redirection retour + code à usage unique</div>
    <div v-click="3" class="seq__msg seq__msg--back seq__msg--dashed" style="--row: 5; --left: 0; --span: 1">code</div>
    <div v-click="4" class="seq__msg" style="--row: 6; --left: 0; --span: 2">code</div>
    <div v-click="4" class="seq__msg seq__msg--back seq__msg--dashed" style="--row: 7; --left: 0; --span: 2">✅ access token</div>
    <div v-click="5" class="seq__msg" style="--row: 8; --left: 0; --span: 2">GET /photos + access token</div>
    <div v-click="5" class="seq__msg seq__msg--back seq__msg--dashed" style="--row: 9; --left: 0; --span: 2">🌁 Les photos d'Alice</div>
  </div>
</div>

<style scoped>
/* Diagramme de séquence maison : trois lignes de vie, un message par ligne, chaque
   message est une cible v-click. À l'apparition, le trait se trace depuis son
   émetteur puis la pointe se pose : la salle voit le flow avancer d'un temps à l'autre. */
.seq {
  --row-h: 1.8rem;
  margin-top: 0.3rem;
  font-size: 0.88rem;
}
.seq__actors {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
}
.seq__actor {
  justify-self: center;
  padding: 0.3rem 1rem;
  border: 2px solid var(--sec);
  border-radius: 0.7rem;
  background: rgba(var(--a-1-rgb), 0.08);
  font-family: "Sora", var(--font-emoji), sans-serif;
  font-weight: 700;
  font-size: 0.95rem;
  text-align: center;
}
.seq__actor small {
  display: block;
  font-family: "Inter", sans-serif;
  font-weight: 500;
  font-size: 0.72rem;
  color: var(--c-muted);
}
.seq__body {
  position: relative;
  height: calc(var(--row-h) * 10.3);
}
.seq__life {
  position: absolute;
  top: 0;
  bottom: 0;
  left: calc((var(--lane) + 0.5) * 100% / 3);
  border-left: 2px dashed var(--c-border-strong);
}
/* Un message occupe la largeur entre deux lignes de vie ; le trait vit dans ::before,
   la pointe dans ::after. --back inverse le sens (vers la gauche). */
.seq__msg {
  position: absolute;
  top: calc(var(--row) * var(--row-h));
  left: calc((var(--left) + 0.5) * 100% / 3);
  width: calc(var(--span) * 100% / 3);
  padding-bottom: 0.85rem;
  text-align: center;
  line-height: 1.2;
  white-space: nowrap;
}
.seq__msg::before {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0.3rem;
  border-top: 2px solid var(--c-fg);
  transform-origin: left center;
  transition: transform 0.45s ease;
}
.seq__msg--dashed::before { border-top-style: dashed; }
.seq__msg--back::before { transform-origin: right center; }
.seq__msg::after {
  content: '';
  position: absolute;
  right: -1px;
  bottom: 0.3rem;
  transform: translateY(50%);
  border: 6px solid transparent;
  border-right: 0;
  border-left: 10px solid var(--c-fg);
  transition: opacity 0.2s ease 0.35s;
}
.seq__msg--back::after {
  right: auto;
  left: -1px;
  border-left: 0;
  border-right: 10px solid var(--c-fg);
}
.seq__msg.slidev-vclick-hidden::before { transform: scaleX(0); }
.seq__msg.slidev-vclick-hidden::after { opacity: 0; }
.seq__note {
  position: absolute;
  top: calc(var(--row) * var(--row-h) + 0.1rem);
  left: calc((var(--lane) + 0.5) * 100% / 3);
  transform: translateX(-50%);
  padding: 0.2rem 0.7rem;
  background: #fff4c2;
  border: 1px solid #e4c65b;
  border-radius: 0.45rem;
  font-size: 0.8rem;
  color: #5a4a00;
  white-space: nowrap;
}
</style>

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
    <b>Client public</b><br/>
    Une SPA qui tourne chez Alice, sans secret possible. Demande les photos <b>pour le compte</b> d'Alice.
  </Card>
  <Card v-click :accent="3" icon="☁️" title="CloudPics">
    <b>Authorization server<br/>+ Resource server</b><br/>
    Émet les access tokens, et héberge les photos.
  </Card>
</CardGrid>

<v-click>

<div class="slide-note">Un client <b>public</b> tourne chez l'utilisateur (SPA, mobile) et ne peut garder aucun secret. Un client <b>confidentiel</b> tourne sur son propre serveur et s'authentifie avec un <code>client_secret</code>.</div>

</v-click>

<style scoped>
.oauth-roles { margin-top: 1rem; align-items: stretch; }
.oauth-roles :deep(.ds-card__icon) { font-size: 2.7rem; }
</style>

---
layout: default
class: sec-authz
---

# La première faiblesse 

Le code d'autorisation transite par le navigateur, dans l'URL de redirection

<v-clicks>

- 🌐 **Web** : code dans l'**URL de redirection** → historique, logs, `Referer`
- 📱 **Mobile** : une app malveillante prend le **même URL scheme** (`monapp://`) → capte le code

</v-clicks>

<v-click>

<div class="slide-punch">Le code d'autorisation peut être <b>volé</b>.<br/>Et un client public n'a aucun secret pour compenser.</div>

</v-click>

---
layout: default
class: sec-authz
---

# La parade : PKCE

**P**roof **K**ey for **C**ode **E**xchange

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

**OAuth 2.1** (toujours un draft IETF) : PKCE obligatoire pour **tous** les clients.

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
- 🔒 **TLS obligatoire** : sur le réseau, lire le token, c'est pouvoir l'utiliser
- 🙈 **Jamais dans l'URL**, même en HTTPS : historique, logs serveur et `Referer` la conservent en clair

</v-clicks>

<v-click>

<div class="slide-punch">Par défaut, rien n'empêche l'usage d'un token volé.<br/>On ne corrige pas ça, on <b>limite sa durée de validité</b>.</div>

</v-click>

<!--
Le mot à ne pas lâcher : « au porteur ». C'est un billet de concert, pas une carte
d'identité. Le contrôleur vérifie le billet, jamais qui le présente.

C'est exactement la faiblesse que PKCE corrigeait pour le code d'autorisation,
sauf qu'ici elle reste. D'où l'enchaînement sur les durées de vie et le refresh.

Si on demande comment faire mieux : DPoP (RFC 9449, Standards Track) lie le token
à une clé cryptographique du client, ce qui rend un token volé inutilisable.
Même idée que PKCE, appliquée au token. Ce n'est pas le comportement par défaut,
et Symfony ne le gère pas nativement.
-->
