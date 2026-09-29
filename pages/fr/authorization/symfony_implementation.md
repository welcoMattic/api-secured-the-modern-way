---
layout: default
class: sec-authz
---

# Votre API peut être son propre serveur OAuth2

<v-clicks>

- 📦 `league/oauth2-server-bundle`
- 🏛️ Votre API = **authorization server + resource server**
- 🎫 Émission + validation des tokens, contrôle des **scopes**

</v-clicks>

<v-click>

<div class="slide-punch">Possible. Pas ce que je recommande.</div>

</v-click>

---
layout: default
class: sec-authz
---

# Le bundle ne fait pas tout à votre place

<v-clicks>

- 👤 **Login d'Alice** : `/authorize` exige un utilisateur connecté, le formulaire de login reste à coder
- 🙋 **Consentement** : le bundle émet un événement, l'écran « Autorises-tu PhotoPrint ? » reste à coder
- 🔑 **Rotation** des clés de signature
- ⛓️ **MFA**, mot de passe oublié, révocation de sessions
- 📊 **Audit** : qui a autorisé quoi, et quand

</v-clicks>

---
layout: statement
class: sec-authz
---

# Vous maintenez un serveur d'autorisation

C'est un **produit à part entière**. <br>
Trop lié à votre API : l'un **peut faire tomber** l'autre
