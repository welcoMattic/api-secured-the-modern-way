---
layout: statement
class: sec-authz
---

# OAuth2

**Delegating access** to your resources, <br> **without sharing your credentials**.

---
layout: statement
class: sec-authz
---

## 👀 {.!text-7xl .mb-6}

# Today's focus

The **on behalf of the user** scenario <br> (Authorization Code Flow).

<span class="text-base italic opacity-60">With no user in the loop, an app calling an API on its own behalf: <br> *Client Credentials Flow*, out of scope today.</span>

---
layout: default
class: sec-authz
---

# Alice, PhotoPrint and CloudPics

<div class="story">

<div class="beat" v-click>
  <div class="beat__label">The setting</div>
  <div class="beat__body">
    👩‍🦰 Alice stores her photos on <b>CloudPics</b>.<br/>
    📸 From <b>PhotoPrint</b>, a third-party web app, <br> she wants to <b>get her CloudPics photos printed</b>.
  </div>
</div>

<div class="beat" v-click>
  <div class="beat__label">The constraint</div>
  <div class="beat__body">
    🔐 Without ever asking her for her <b>CloudPics password</b>.
  </div>
</div>

<div class="beat" v-click>
  <div class="beat__label">The flow</div>
  <div class="beat__body">
    🔗 PhotoPrint <b>redirects</b> Alice to CloudPics, which asks for her approval.<br/>
    <blockquote class="mt-2 mb-4 !text-base">Do you allow PhotoPrint to access your CloudPics photos?</blockquote>
    🔢 Alice <b>approves</b>: CloudPics issues a single-use <b>code</b>.<br/>
    🔄 PhotoPrint <b>exchanges</b> that code for an <b>access token</b><br/>
    🌁 PhotoPrint <b>accesses</b> Alice's photos with the access token
  </div>
</div>

</div>

<v-click>

<Alert type="info">

**CloudPics** plays the OAuth2 server, and **PhotoPrint** the client application

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

# The flow, step by step

<div class="seq">
  <div class="seq__actors">
    <div class="seq__actor">🌁 PhotoPrint <small>client</small></div>
    <div class="seq__actor">👩‍🦰 Alice <small>browser</small></div>
    <div class="seq__actor">☁️ CloudPics <small>OAuth2 server</small></div>
  </div>
  <div class="seq__body">
    <div class="seq__life" style="--lane: 0"></div>
    <div class="seq__life" style="--lane: 1"></div>
    <div class="seq__life" style="--lane: 2"></div>
    <div v-click="1" class="seq__msg seq__msg--back" style="--row: 0; --left: 0; --span: 1">"Print my CloudPics photos"</div>
    <div v-click="1" class="seq__msg" style="--row: 1; --left: 0; --span: 1">Redirect to CloudPics</div>
    <div v-click="2" class="seq__msg" style="--row: 2; --left: 1; --span: 1">Authorization request</div>
    <div v-click="2" class="seq__note" style="--row: 3; --lane: 2">Alice authenticates and consents</div>
    <div v-click="3" class="seq__msg seq__msg--back seq__msg--dashed" style="--row: 4; --left: 1; --span: 1">Redirect back + single-use code</div>
    <div v-click="3" class="seq__msg seq__msg--back seq__msg--dashed" style="--row: 5; --left: 0; --span: 1">code</div>
    <div v-click="4" class="seq__msg" style="--row: 6; --left: 0; --span: 2">code</div>
    <div v-click="4" class="seq__msg seq__msg--back seq__msg--dashed" style="--row: 7; --left: 0; --span: 2">✅ access token</div>
    <div v-click="5" class="seq__msg" style="--row: 8; --left: 0; --span: 2">GET /photos + access token</div>
    <div v-click="5" class="seq__msg seq__msg--back seq__msg--dashed" style="--row: 9; --left: 0; --span: 2">🌁 Alice's photos</div>
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

# The stakeholders

<CardGrid :cols="3" class="oauth-roles">
  <Card v-click :accent="1" icon="👩‍🦰" title="Alice">
    <b>Resource owner</b><br/>
    She alone can grant access to her photos.
  </Card>
  <Card v-click :accent="2" icon="🌁" title="PhotoPrint">
    <b>Public client</b><br/>
    An SPA running on Alice's device, with no way to keep a secret. Asks for the photos <b>on behalf of</b> Alice.
  </Card>
  <Card v-click :accent="3" icon="☁️" title="CloudPics">
    <b>Authorization server<br/>+ Resource server</b><br/>
    Issues the access tokens, and stores the photos.
  </Card>
</CardGrid>

<v-click>

<div class="slide-note">A <b>public</b> client runs on the user's device (SPA, mobile) and cannot keep any secret. A <b>confidential</b> client runs on its own server and authenticates with a <code>client_secret</code>.</div>

</v-click>

<style scoped>
.oauth-roles { margin-top: 1rem; align-items: stretch; }
.oauth-roles :deep(.ds-card__icon) { font-size: 2.7rem; }
</style>

---
layout: default
class: sec-authz
---

# The first weakness 

The authorization code travels through the browser, in the redirect URL

<v-clicks>

- 🌐 **Web**: code in the **redirect URL** → history, logs, `Referer`
- 📱 **Mobile**: a malicious app claims the **same URL scheme** (`myapp://`) → captures the code

</v-clicks>

<v-click>

<div class="slide-punch">The authorization code can be <b>stolen</b>.<br/>And a public client has no secret to make up for it.</div>

</v-click>

---
layout: default
class: sec-authz
---

# The countermeasure: PKCE

**P**roof **K**ey for **C**ode **E**xchange

<v-clicks>

- 🎲 **code_verifier**: a random secret, known only to the client
- 🔒 **code_challenge**: its SHA-256 digest, sent first

</v-clicks>

<v-click>

<div class="slide-punch">The challenge is <b>irreversible</b>: without the verifier, a stolen code is worthless.</div>

</v-click>

---
layout: default
class: sec-authz
---

# PKCE as a sequence

```mermaid
%%{init: {"sequence": {"messageMargin": 16, "boxMargin": 4, "noteMargin": 4, "diagramMarginY": 0}}}%%
sequenceDiagram
    participant P as 🌁 PhotoPrint (client)
    participant A as 👩‍🦰 Alice (browser)
    participant C as ☁️ CloudPics (authZ server)

    Note over P: Generates code_verifier<br/>code_challenge = SHA-256(verifier)
    P->>A: Redirect to CloudPics
    A->>C: Authorization request + code_challenge + algorithm
    Note over C: Alice authenticates and consents<br/>authorization code issued, bound to the challenge
    C-->>A: Redirect back + authorization code
    A-->>P: authorization code
    P->>C: authorization code + code_verifier (direct request, TLS)
    Note over C: SHA-256(verifier) = challenge?
    C-->>P: ✅ access token
```

<v-click>

<Alert type="info">

**OAuth 2.1** (still an IETF draft): PKCE mandatory for **every** client.

</Alert>

</v-click>

---
layout: default
class: sec-authz
---

# The token proves nothing: holding it is enough

```http
GET /api/photos HTTP/1.1
Host: api.cloudpics.example
Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...
```

<v-clicks>

- 🎫 **Bearer** = "to whoever holds it": no proof is ever asked of the client
- 🕵️ Once stolen, it is **indistinguishable** from a legitimate token. The API cannot tell
- 🔒 **TLS is mandatory**: on the wire, reading the token means being able to use it
- 🙈 **Never in the URL**, even over HTTPS: history, server logs and `Referer` keep it in the clear

</v-clicks>

<v-click>

<div class="slide-punch">By default, nothing stops a stolen token from being used.<br/>You don't fix that, you <b>keep its lifetime short</b>.</div>

</v-click>

<!--
The word to land: "bearer". It is a concert ticket, not an ID card. The usher
checks the ticket, never who is holding it.

This is exactly the weakness PKCE fixed for the authorization code, except here
it stays. Hence the move to token lifetimes and refresh.

If someone asks how to do better: DPoP (RFC 9449, Standards Track) binds the token
to a cryptographic key held by the client, which makes a stolen token useless.
Same idea as PKCE, applied to the token. It is not the default behaviour, and
Symfony has no native support for it.
-->
