---
layout: default
---

# Que veut dire "Sécurisé" ?

<div class="grid grid-cols-3 gap-8 mt-8">

<div v-click class="text-center p-6 bg-green-50 rounded-xl border-2 border-green-200">
  <div class="text-4xl mb-4">🛡️</div>
  <h3 class="text-xl font-bold text-green-800 mb-2">Autorisation <br/> <small>(OAuth2)</small></h3>
  <p class="text-sm text-green-700"><b>Que</b> puis-je faire ?</p>
  <p class="text-xs text-gray-600 mt-2">Rôles, permissions,<br/>contrôle d'accès</p>
</div>

<div v-click class="text-center p-6 bg-blue-50 rounded-xl border-2 border-blue-200">
  <div class="text-4xl mb-4">🔐</div>
  <h3 class="text-xl font-bold text-blue-800 mb-2">Authentification <br/> <small>(OIDC)</small></h3>
  <p class="text-sm text-blue-700"><b>Qui</b> êtes-vous ?</p>
  <p class="text-xs text-gray-600 mt-2">user/password → tokens<br/>client_id/secret → tokens</p>
</div>

<div v-click class="text-center p-6 bg-amber-50 rounded-xl border-2 border-amber-200">
  <div class="text-4xl mb-4">⏱️</div>
  <h3 class="text-xl font-bold text-amber-800 mb-2">Rate Limiting <br/> <small>(X-RateLimit-Limit)</small></h3>
  <p class="text-sm text-amber-700"><b>Combien</b> je peux faire ?</p>
  <p class="text-xs text-gray-600 mt-2">Protéger les ressources <br/>Usage raisonnable</p>
</div>

</div>
