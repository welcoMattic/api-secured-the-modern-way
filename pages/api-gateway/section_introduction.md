---
layout: section
class: sec-gw
---

<div class="section-index">🎁</div>

# L'API Gateway

La surprise !

---
layout: default
class: sec-gw
---

# Est-ce vraiment le job de votre API ?

<v-clicks>

- 🔐 Auth, autorisation, rate limiting, protection...
- ⏳ Un **travail à plein temps**
- 🛎️ **API Gateway / API Management** : c'est leur métier

</v-clicks>

---
layout: default
class: sec-gw
---

# Choisissez votre préférée !

### Open source ou commerciale, auto-hébergée ou SaaS, le choix est vaste !

<ServiceGroup europe label="Europe" :cols="4" class="mt-4">
  <Logo :size="3" src="/gravitee.webp" label="Gravitee" />
  <Logo :size="3" src="/krakend.svg" label="KrakenD" />
  <Logo :size="3" src="/otoroshi.png" label="Otoroshi" />
  <Logo :size="3" src="/traefik.png" label="Traefik" />
</ServiceGroup>

<ServiceGroup label="Reste du monde" :cols="4" class="mt-4">
  <Logo :size="3" src="/aws-api-gateway.svg" label="Amazon API Gateway" />
  <Logo :size="3" src="/apigee.png" label="Apigee" />
  <Logo :size="3" src="/apisix.svg" label="Apisix" />
  <Logo :size="3" src="/konghq.webp" label="Kong" />
</ServiceGroup>

---
layout: default
class: sec-gw
---

# Déléguez !

Trois domaines entiers que votre API n'a plus à porter.

<CardGrid :cols="3" class="gw-grid">
  <Card v-click :accent="1" icon="🔐" title="Accès">
    Authentification<br/>
    Autorisation<br/>
    CORS (préflight + en-têtes)
  </Card>
  <Card v-click :accent="4" icon="🛡️" title="Protection">
    WAF : SQLi, XSS, OWASP<br/>
    Détection de bots<br/>
    IP allow/deny, géo-blocage
  </Card>
  <Card v-click :accent="7" icon="📊" title="Exploitation">
    Rate limiting<br/>
    Logging<br/>
    Monitoring
  </Card>
</CardGrid>

<v-click>

<div class="gw-punch">Tout ça <b>en amont</b> de votre API<br/> et sans <b>"polluer"</b> votre code applicatif.</div>

</v-click>

<style scoped>
/* Takeaway slide: the grouping is the insight, the punchline is the payoff. */
.gw-grid {
  margin-top: 1.1rem;
  align-items: stretch;
}
.gw-grid :deep(.ds-card__icon) { font-size: 2.7rem; }
.gw-punch {
  margin-top: 1.5rem;
  text-align: center;
  font-family: "Sora", sans-serif;
  font-size: 1.7rem;
  font-weight: 800;
  line-height: 1.25;
  color: var(--c-fg);
  text-wrap: balance;
}
.gw-punch b { color: var(--sec, var(--c-accent)); }
</style>

<!--
Liste non exhaustive : la plupart des gateways couvrent bien plus.

CORS, à ne pas survoler : c'est le navigateur qui applique la Same-Origin Policy, pas votre API. La gateway répond aux requêtes préflight (OPTIONS) et ajoute les en-têtes Access-Control-Allow-*. L'intérêt : une politique cross-origin centralisée et cohérente sur tout le parc, au lieu de reconfigurer nelmio/cors-bundle dans chaque service Symfony.

Message clé : trois domaines entiers sortent de votre code applicatif. C'est le take away de la section.
-->
