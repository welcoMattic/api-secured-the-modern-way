---
layout: default
---

# OIDC ajoute une couche d'identité à OAuth2

<v-clicks>

- 🆔 **ID Tokens** : des JWT contenant les claims d'identité de l'utilisateur
- 📋 **Claims standards** : sub, aud, iss, exp, etc.
- 🔗 **Discovery** : endpoint de configuration « well-known »
- 🔐 **UserInfo** : endpoint fournissant des données utilisateur supplémentaires

</v-clicks>

---
layout: statement
---

# OIDC Provider

Contrairement au bundle serveur OAuth2 qui intègre un authorization server dans le resource server,
OIDC recommande de s'appuyer sur un **OIDC Provider** séparé qui émet les ID tokens, les user info et les access tokens.

---
layout: default
---

# Les JW* expliqués {class="!mb-4"}

| Acronyme   | Nom complet                        | Rôle                                                 | Métaphore                                    | Est-ce un token ?                                              |
|------------|------------------------------------|------------------------------------------------------|----------------------------------------------|----------------------------------------------------------------|
| **JWT**    | **J**SON **W**eb **T**oken         | Définit la structure des claims dans le payload.     | Le courrier ou le contenu du colis           | Oui, bien qu'il ne soit jamais utilisé sans structure JWS/JWE. |
| **JWS**    | **J**SON **W**eb **S**ignature     | Fournit l'intégrité et l'authenticité via une signature. | Une enveloppe transparente avec un sceau inviolable | Oui, un token signé.                                    |
| **JWE**    | **J**SON **W**eb **E**ncryption    | Fournit la confidentialité via le chiffrement.       | Une boîte métallique opaque et verrouillée   | Oui, un token chiffré.                                         |

---
layout: default
---

# Les JW* expliqués {class="!mb-4"}

| Acronyme   | Nom complet                        | Rôle                                                 | Métaphore                                    | Est-ce un token ?                              |
|------------|------------------------------------|------------------------------------------------------|----------------------------------------------|------------------------------------------------|
| **JWA**    | **J**SON **W**eb **A**lgorithms    | Définit les algorithmes cryptographiques autorisés.  | La liste des types de serrures/sceaux approuvés | Non, c'est une liste de noms.               |
| **JWK(S)** | **J**SON **W**eb **K**ey (**S**et) | Un format standard pour représenter une (un ensemble de) clé(s). | La (les) clé(s) elle(s)-même(s) et ses métadonnées | Non, c'est un format de (jeu de) clé(s). |
