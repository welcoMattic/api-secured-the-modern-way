---
layout: default
class: sec-authz
---

# Your API can be its own OAuth2 server

<v-clicks>

- 📦 `league/oauth2-server-bundle`
- 🏛️ Your API = **authorization server + resource server**
- 🎫 Issuing + validating tokens, enforcing **scopes**

</v-clicks>

<v-click>

<div class="slide-punch">Doable. Not what I recommend.</div>

</v-click>

---
layout: default
class: sec-authz
---

# The bundle does not do it all for you

<v-clicks>

- 👤 **Alice's login**: `/authorize` requires an authenticated user, the login form is still yours to write
- 🙋 **Consent**: the bundle dispatches an event, the "Do you allow PhotoPrint?" screen is still yours to write
- 🔑 **Rotating** the signing keys
- ⛓️ **MFA**, forgotten password, session revocation
- 📊 **Audit**: who authorized what, and when

</v-clicks>

---
layout: statement
class: sec-authz
---

# You are maintaining an authorization server

It is a **product of its own**. <br>
Too tightly coupled to your API: one **can take down** the other
