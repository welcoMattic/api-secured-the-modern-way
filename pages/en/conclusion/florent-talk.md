---
layout: default
---

# Go see Florent tomorrow at noon!

<div class="talk-title">HTTP headers as the first line of defence for APIs and front ends</div>

<div class="talk-meta">
  <span class="talk-speaker">Florent Morselli</span>
  <span>Room 1</span>
  <span>11:50 - 12:10</span>
</div>

<img class="talk-shot" src="/florent-talk.png" alt="Florent Morselli's talk in the API Platform Con 2026 schedule: day 2, room 1, 11:50" />

<div class="slide-punch">He covers other sides of security, with plenty of other funny acronyms: CSP, CORS, COOP, COEP.</div>

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
