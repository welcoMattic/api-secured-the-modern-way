---
layout: section
class: sec-gw
---

<div class="section-index">🎁</div>

# The API Gateway

The surprise!

---
layout: default
class: sec-gw
---

# Is this really your API's job?

<v-clicks>

- 🔐 Auth, authorization, rate limiting, protection...
- ⏳ A **full-time job**
- 🛎️ **API Gateway / API Management**: that is their whole business

</v-clicks>

---
layout: default
class: sec-gw
---

# Pick your favourite!

Open source or commercial, self-hosted or SaaS, there is plenty to choose from!

<ServiceGroup europe label="European origin" :cols="4" class="mt-4">
  <Logo :size="3" src="/gravitee.webp" label="Gravitee" />
  <Logo :size="3" src="/krakend.svg" label="KrakenD" />
  <Logo :size="3" src="/otoroshi.png" label="Otoroshi" />
  <Logo :size="3" src="/traefik.png" label="Traefik" />
</ServiceGroup>

<ServiceGroup label="Rest of the world" :cols="4" class="mt-4">
  <Logo :size="3" src="/aws-api-gateway.svg" label="Amazon API Gateway" />
  <Logo :size="3" src="/apigee.png" label="Apigee" />
  <Logo :size="3" src="/apisix.svg" label="Apisix" />
  <Logo :size="3" src="/konghq.webp" label="Kong" />
</ServiceGroup>

---
layout: default
class: sec-gw
---

# Delegate!

Three entire domains your API no longer has to carry.

<CardGrid :cols="3" class="gw-grid">
  <Card v-click :accent="1" icon="🔐" title="Access">
    Token verification<br/>
    Coarse-grained authorization: scopes, routes<br/>
    CORS (preflight + headers)
  </Card>
  <Card v-click :accent="4" icon="🛡️" title="Protection">
    WAF: SQLi, XSS, OWASP<br/>
    Bot detection<br/>
    IP allow/deny, geo-blocking
  </Card>
  <Card v-click :accent="7" icon="📊" title="Operations">
    Rate limiting<br/>
    Logging<br/>
    Monitoring
  </Card>
</CardGrid>

<v-click>

<div class="slide-punch is-centered">All of it <b>upstream</b> of your API<br/> and without <b>"polluting"</b> your application code.</div>

</v-click>

<v-click>

<div class="slide-note is-centered">The API keeps <code>is_granted</code> and the ownership check, and re-verifies the JWT: offline, that costs nothing.</div>

</v-click>

<style scoped>
/* Takeaway slide: the grouping is the insight, the punchline is the payoff. */
.gw-grid {
  margin-top: 1.1rem;
  align-items: stretch;
}
.gw-grid :deep(.ds-card__icon) { font-size: 2.7rem; }
</style>

<!--
Non-exhaustive list: most gateways cover far more than this.

CORS, not to be glossed over: it is the browser that enforces the Same-Origin Policy, not your API. The gateway answers preflight requests (OPTIONS) and adds the Access-Control-Allow-* headers. The benefit: one centralized, consistent cross-origin policy across the whole estate, instead of reconfiguring nelmio/cors-bundle in every Symfony service.

Key message: three entire domains leave your application code. That is the takeaway of this section.
-->
