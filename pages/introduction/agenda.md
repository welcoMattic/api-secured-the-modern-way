---
layout: default
class: promise
---

# Trois briques standards sécurisent votre API

Du flow authorization_code jusqu'au contrôle d'accès sur vos ressources.

<v-clicks>

- 🛡 **Autorisation** avec OAuth2 <span class="promise-pkg">league/oauth2-server-bundle</span>
- 🔐 **Authentification** avec OpenID Connect <span class="promise-pkg">authenticator access_token, natif Symfony</span>
- ⏱️ **Rate Limiting** <span class="promise-pkg">composant Symfony RateLimiter</span>

</v-clicks>

<style scoped>
/* Promise slide: big punchy items, with the concrete package underneath. */
.promise ul > li {
  font-size: 2rem;
  line-height: 1.2;
  margin-bottom: 1.35rem;
}
.promise-pkg {
  display: block;
  margin-top: .3rem;
  font-family: "Fira Code", monospace;
  font-size: .85rem;
  color: var(--c-muted);
}
.promise-foot {
  margin-top: 1.2rem;
  font-size: 1.2rem;
}
</style>
