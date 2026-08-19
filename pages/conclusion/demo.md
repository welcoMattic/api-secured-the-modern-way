---
layout: default
---

# La démo est à vous

<div class="demo-url">github.com/welcoMattic/api-secured-the-modern-way</div>

```bash
cd demo

castor start           # les 4 acteurs, en local
cd infra && tofu apply # les mêmes, sur Clever Cloud
```

<div class="slide-punch">Un client public, un client confidentiel, <b>le même Provider</b>.</div>

<div class="slide-note">L'apply crée l'add-on Keycloak, les trois apps et leurs variables, puis déploie le code. Clever attribue les domaines, il n'y a rien à réserver. Seul l'import du realm reste un script.</div>

<style scoped>
/* The URL is what the room writes down, so it outranks the title's own weight.
   It must never wrap: a broken repo path is a repo path nobody types. */
.demo-url {
  margin: 0.5rem 0 1.6rem;
  font-family: var(--slidev-code-font-family, monospace);
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--c-accent);
  white-space: nowrap;
  letter-spacing: -0.02em;
}
</style>
