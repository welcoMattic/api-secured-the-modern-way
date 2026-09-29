---
layout: default
---

# Allez voir Florent demain midi !

<div class="talk-title">Les en-têtes HTTP comme première ligne de défense des APIs et du front</div>

<div class="talk-meta">
  <span class="talk-speaker">Florent Morselli</span>
  <span>Salle 1</span>
  <span>11h50 - 12h10</span>
</div>

<img class="talk-shot" src="/florent-talk.png" alt="Le talk de Florent Morselli dans le programme de l'API Platform Con 2026 : jour 2, salle 1, 11h50" />

<div class="slide-punch">Il couvre d'autres aspects de sécurité avec plein d'autres acronymes rigolos : CSP, CORS, COOP, COEP.</div>

<style scoped>
/* A talk title is prose, not a path: unlike .demo-url it has to wrap,
   so it balances across lines instead of being forced onto one. */
.talk-title {
  margin: 0.4rem 0 1rem;
  font-family: "Sora", sans-serif;
  font-size: 2rem;
  font-weight: 800;
  line-height: 1.15;
  color: var(--c-accent);
  text-wrap: balance;
}
/* The meta row echoes .closing-contact: spans in a flex row where the gap is
   the only separator, so no punctuation has to carry the layout. */
.talk-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem 1.5rem;
  font-size: 1.02rem;
  color: var(--c-muted);
}
/* The program card is the proof the room can check: framed like the PR shot. */
.talk-shot {
  display: block;
  width: 100%;
  max-width: 40rem;
  margin: 1rem 0 1.2rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--c-border);
  box-shadow: var(--shadow-card);
}
.talk-speaker {
  font-weight: 700;
  color: var(--c-fg);
}
</style>
