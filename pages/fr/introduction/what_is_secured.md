---
layout: default
class: pillars
---

# Une API "Sécurisée" ?

<CardGrid :cols="2" class="pillars-grid">
  <Pillar v-click="1" :accent="1" icon="🛡️" title="Autorisation" tag="OAuth2"
          question="Que" lead="puis-je faire ?">
    Déléguer l'accès : scopes, permissions
  </Pillar>
  <Pillar v-click="2" :accent="4" icon="🔐" title="Authentification" tag="OIDC"
          question="Qui" lead="êtes-vous ?">
    Alice prouve qui elle est, l'app reçoit des tokens
  </Pillar>
</CardGrid>

<div class="rest">
  <span class="chip" v-click="4">WAF</span>
  <span class="chip" v-click="4">OWASP API Top 10</span>
  <span class="chip is-near" v-click="4">Détection de bots</span>
  <span class="chip chip--focus" v-click="3">⏱️ Rate limiting</span>
  <span class="chip is-near" v-click="4">Anti-DDoS</span>
  <span class="chip" v-click="4">IP / Geo blocking</span>
  <span class="chip" v-click="4">mTLS</span>
  <span class="chip" v-click="4">CORS</span>
</div>

<div class="slide-punch is-centered" v-click="4">Et bien plus…</div>

<style scoped>
/* Two pillars now carry the talk: same card size as before, narrower row so the
   two of them read wide and centered. No min-height: `.ds-pillar` is
   `height: 100%`, so a min-height shorter than the content makes the cards
   overflow the grid instead of growing it. */
.pillars-grid {
  /* the h1 already carries 1.1rem of bottom margin */
  margin-top: 0;
  margin-inline: auto;
  max-width: 44rem;
  align-items: stretch;
}

/* Depth of field: everything else in API security sits out of focus behind the
   two pillars. Blur varies slightly so it reads as a lens, not as a glitch. */
.rest {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: .3rem .4rem;
  max-width: 56rem;
  margin: .6rem auto 0;
}
.chip {
  font-size: .76rem;
  font-weight: 600;
  line-height: 1.2;
  color: var(--c-muted);
  background: rgba(87, 99, 122, .08);
  border: 1px solid rgba(87, 99, 122, .16);
  border-radius: var(--radius-pill);
  padding: .2rem .66rem;
  filter: blur(var(--b, 3.6px));
  opacity: .55;
}
.chip.is-near { --b: 2.2px; }

/* The one exception, in focus from the start: it gets three slides. */
.chip--focus {
  --b: 0px;
  opacity: 1;
  font-size: .84rem;
  font-weight: 800;
  color: var(--a-7);
  background: rgba(var(--a-7-rgb), .12);
  border-color: rgba(var(--a-7-rgb), .45);
}

.slide-punch { margin-top: .3rem; }
</style>
