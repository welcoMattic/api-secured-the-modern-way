---
layout: default
class: pillars
---

# Une API "Sécurisée" ?

<CardGrid :cols="3" class="pillars-grid">
  <Pillar v-click :accent="1" icon="🛡️" title="Autorisation" tag="OAuth2"
          question="Que" lead="puis-je faire ?">
    Déléguer l'accès : scopes, permissions
  </Pillar>
  <Pillar v-click :accent="4" icon="🔐" title="Authentification" tag="OIDC"
          question="Qui" lead="êtes-vous ?">
    Alice prouve qui elle est, l'app reçoit des tokens
  </Pillar>
  <Pillar v-click :accent="7" icon="⏱️" title="Rate Limiting" tag="Quota"
          question="Combien" lead="puis-je faire ?">
    Protéger les ressources, usage raisonnable
  </Pillar>
</CardGrid>

<v-click>

<div class="slide-punch is-centered">Et bien plus…</div>

</v-click>

<style scoped>
/* Hero slide: the three pillars carry the whole talk, so let them breathe. */
.pillars-lead {
  font-size: 1.15rem;
  color: var(--c-muted);
}
.pillars-grid {
  margin-top: 1.4rem;
  align-items: stretch;
  min-height: 18.5rem;
}
</style>
