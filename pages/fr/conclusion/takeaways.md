---
layout: default
---

# À retenir

<CardGrid :cols="2" class="mt-6">
  <Card v-click :accent="1" badge="1" title="Les standards existent : utilisez-les">
    JWT, OAuth 2.0, OIDC. <b>N'inventez pas le vôtre.</b>
  </Card>
  <Card v-click :accent="3" badge="2" title="Les outils modernes simplifient tout">
    Symfony Security, League, l'authenticator <code>access_token</code>, les OIDC Providers. <b>Le gros du travail est déjà fait.</b>
  </Card>
  <Card v-click :accent="5" badge="3" title="La sécurité est une fonctionnalité">
    Pas une option, pas un « nice to have ». <b>Dès le premier jour.</b>
  </Card>
  <Card v-click :accent="7" badge="4" title="Équipez vos APIs">
    API Gateway / API Management. <b>Déléguez ce qui peut l'être.</b>
  </Card>
</CardGrid>
