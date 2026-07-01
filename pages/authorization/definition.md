---
layout: statement
---

# Définition

OAuth2 est une **spécification technique** qui permet aux **utilisateurs de donner des permissions à des applications** 
pour accéder à des ressources protégées **sans divulguer leurs identifiants**.

---
layout: statement
---

# Définition

👀 {.text-6xl}
<br>
**Aujourd'hui, nous nous concentrerons sur le scénario "au nom de l'utilisateur"**, <br> mais OAuth2 prend aussi en charge les "client credentials" qui n'implique pas d'utilisateur.

<br><br>
[Plus d'infos dans la RFC 6749] {.text-base .italic}

---
layout: default
---

# Cas d'usage basique

<v-clicks>

- 👩‍🦰 Alice, **utilisatrice** des services **PhotoPrint** et **CloudPics**.
- 📸 PhotoPrint a besoin **d'accéder aux photos d'Alice** stockées sur **CloudPics**.
- 🔐 Alice **ne veut pas partager son mot de passe CloudPics** avec PhotoPrint.
- 🔗 PhotoPrint **redirige Alice vers CloudPics** pour autoriser l'accès.
- 🔢 Alice **accorde la permission**, et PhotoPrint reçoit un **code** valide pour une seule utilisation.
- 🔄 PhotoPrint **échange le code contre un token d'accès** auprès de CloudPics.
- ✅ PhotoPrint utilise le **token d'accès** pour accéder aux photos d'Alice sur CloudPics.

</v-clicks>

<v-click>

<Alert type="info">

Le **développeur de l'API CloudPics** doit implémenter OAuth2 afin qu'Alice accorde l'accès à ses photos à PhotoPrint.

</Alert>

</v-click>

---
layout: iframe
url: https://s.icepanel.io/GPg85hHAHAN9PQ/prFj
---

---
layout: default
---

# Cas d'usage OAuth2

## Dans ce scénario {.mb-12}

<v-clicks>

- ☁️ *CloudPics* est le **resource server** qui héberge les photos d'Alice et l'**authorization server** qui émet les tokens.
- 🌁 *PhotoPrint* est la **client application** qui veut accéder à une ressource protégée.
- 👩‍🦰 *Alice* est la **resource owner** des photos.

</v-clicks>
