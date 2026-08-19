---
layout: default
---

# La démo est à vous

<div class="demo-repo">github.com/welcoMattic/api-secured-the-modern-way</div>

<CardGrid :cols="3" class="mt-2">
  <Card :accent="1" icon="🧩" title="Les quatre acteurs du talk">
    <b>CloudPics ID</b> émet les tokens, <b>CloudPics API</b> les vérifie, <b>PhotoPrint</b> et <b>PhotoBook</b> les demandent.
    Un client public, un client confidentiel, <b>le même Provider</b>.
  </Card>
  <Card :accent="4" icon="💻" title="En local, une commande">
    <code>castor start</code> monte Keycloak, l'API et les deux clients.
    Rien à configurer, les comptes de démo sont importés avec le realm.
  </Card>
  <Card :accent="6" icon="☁️" title="Sur Clever Cloud, un apply">
    <code>tofu apply</code> crée l'add-on Keycloak, les trois apps, leurs domaines et leurs variables,
    <b>puis déploie le code</b>.
  </Card>
</CardGrid>

<div class="demo-note">Une seule étape reste manuelle : l'import du realm. Le provider n'expose pas les identifiants FTP du bucket de l'add-on, donc c'est un script. <b>Tout le reste tient dans le HCL.</b></div>

<style scoped>
/* The repo URL is the one thing to leave on screen, so it carries the weight. */
.demo-repo {
  margin-top: 1.2rem;
  text-align: center;
  font-family: var(--slidev-code-font-family, monospace);
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--a-1);
}
.demo-note {
  margin-top: 1.6rem;
  text-align: center;
  font-size: 0.95rem;
  opacity: 0.75;
}
</style>
