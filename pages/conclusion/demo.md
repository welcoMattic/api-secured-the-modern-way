---
layout: default
---

# The demo is yours

<div class="demo-url">github.com/welcoMattic/api-secured-the-modern-way</div>

<img src="/qrcode.png" alt="QR code to the demo repository" class="demo-qr" />

```bash
cd demo

castor start           # the 4 actors, locally
cd infra && tofu apply # the same ones, on Clever Cloud
```

<div class="slide-punch">One public client, one confidential client, <b>the same Provider</b>.</div>

<div class="slide-note">The apply creates the Keycloak add-on, the three apps and their variables, then deploys the code. Clever assigns the domains, there is nothing to reserve. Only the realm import stays a script.</div>

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

/* Le QR code doit rester scannable depuis le fond de la salle, d'où sa taille.
   Hors flux, sous l'URL, pour ne pas repousser le bloc de commandes vers le bas. */
.demo-qr {
  position: absolute;
  top: 13rem;
  right: 3rem;
  width: 13rem;
  height: auto;
}

/* La colonne de droite appartient au QR code : sans ça le bloc de code, qui a
   un fond, passe par dessus, et la glose vient se lire sous les modules. */
:deep(pre),
.slide-punch,
.slide-note {
  max-width: 68%;
}
</style>
