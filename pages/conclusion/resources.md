---
layout: default
---

# Ressources

<CardGrid :cols="3" class="res-grid">
  <Card :accent="1" icon="📜" title="Spécifications">
    <div><a href="https://datatracker.ietf.org/doc/html/rfc6749">RFC 6749</a> OAuth 2.0</div>
    <div><a href="https://datatracker.ietf.org/doc/html/rfc6750">RFC 6750</a> Bearer Token</div>
    <div><a href="https://datatracker.ietf.org/doc/html/rfc7636">RFC 7636</a> PKCE</div>
    <div><a href="https://datatracker.ietf.org/doc/html/rfc7519">RFC 7519</a> JWT</div>
    <div><a href="https://datatracker.ietf.org/doc/html/rfc7662">RFC 7662</a> Introspection</div>
    <div><a href="https://datatracker.ietf.org/doc/html/rfc8693">RFC 8693</a> Token Exchange</div>
    <div><a href="https://openid.net/connect/">OpenID Connect</a> la spéc OIDC</div>
  </Card>
  <Card :accent="4" icon="📚" title="Documentation">
    <div><a href="https://symfony.com/doc/current/security/access_token.html">Symfony Access Token</a></div>
    <div><a href="https://api-platform.com/docs/symfony/security/">API Platform Security</a></div>
    <div><a href="https://symfony.com/doc/current/rate_limiter.html">Symfony Rate Limiter</a></div>
    <div><a href="https://github.com/thephpleague/oauth2-server-bundle">League OAuth2 Server Bundle</a></div>
    <div><a href="https://github.com/Drenso/symfony-oidc">drenso/symfony-oidc</a></div>
    <div><a href="https://auth0.com/blog/rs256-vs-hs256-whats-the-difference/">RS256 vs HS256</a></div>
  </Card>
  <Card :accent="7" icon="🛠️" title="Outils">
    <div><a href="https://jwt.io">jwt.io</a> debugger JWT</div>
    <div><a href="https://oauth.com/playground">OAuth 2.0 Playground</a></div>
    <div><a href="https://owasp.org/API-Security/">OWASP API Security Top 10</a></div>
  </Card>
</CardGrid>

<style scoped>
/* Resources: dense but scannable. The link carries the accent, the gloss stays muted. */
.res-grid {
  margin-top: 1rem;
  align-items: stretch;
}
.res-grid :deep(.ds-card__body) {
  font-size: 0.95rem;
  line-height: 1.85;
}
.res-grid :deep(a) {
  color: var(--solid);
  font-weight: 650;
  text-decoration: none;
  border-bottom: 1px solid rgba(var(--rgb), .3);
}
</style>
