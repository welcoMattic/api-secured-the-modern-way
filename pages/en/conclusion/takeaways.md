---
layout: default
---

# Takeaways

<CardGrid :cols="2" class="mt-6">
  <Card v-click :accent="1" badge="1" title="The standards exist: use them">
    JWT, OAuth 2.0, OIDC. <b>Don't roll your own.</b>
  </Card>
  <Card v-click :accent="3" badge="2" title="Modern tooling makes it simple">
    Symfony Security, League, the <code>access_token</code> authenticator, the OIDC Providers. <b>Most of the work is already done.</b>
  </Card>
  <Card v-click :accent="5" badge="3" title="Security is a feature">
    Not an option, not a nice to have. <b>From day one.</b>
  </Card>
  <Card v-click :accent="7" badge="4" title="Equip your APIs">
    API Gateway / API Management. <b>Delegate whatever can be delegated.</b>
  </Card>
</CardGrid>
