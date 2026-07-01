---
layout: default
---

# Symfony Rate Limiter : vue d'ensemble

<v-clicks>

- 🛡️ Composant de rate limiting **intégré** à Symfony
- 🎯 Protège contre les **attaques par brute force et DoS**
- 🔧 **Configuration flexible** pour différents cas d'usage
- 📦 Disponible depuis Symfony 5.3, **il y a 5 ans**

</v-clicks>

<Alert type="info" v-click>

Le composant Symfony Rate Limiter fournit une implémentation de l'algorithme token bucket pour limiter le débit des requêtes.

</Alert>

---

# Configurer les Rate Limiters

<v-clicks>

- 📝 Définir les limiters dans `config/packages/rate_limiter.yaml`
- 🔢 **Configurer** les limites, les intervalles et le stockage
- 🎯 Des limiters différents pour **des endpoints différents**
- 🔄 Supporte les algorithmes **sliding window** et **fixed window**

</v-clicks>

<div v-click>

```yaml
# config/packages/rate_limiter.yaml
framework:
    rate_limiter:
        api:
            policy: 'token_bucket'
            limit: 100
            rate: { interval: '1 second', amount: 10 }
```

</div>

---
layout: default
---

# Utiliser les Rate Limiters dans les contrôleurs

<v-clicks>

- 🎯 S'applique à **des actions de contrôleur spécifiques**
- 🛡️ Réponse **HTTP 429 automatique** quand la limite est dépassée
- 🔧 **Personnaliser** le comportement de la réponse

</v-clicks>

---
layout: default
---

# Utiliser les Rate Limiters dans les contrôleurs

```php
class ApiController
{
    public function __construct(
        private RateLimiterFactory $apiLimiter,
    ) {}
    
    public function endpoint(Request $request): JsonResponse
    {
        if (!$this->apiLimiter->create($request->getClientIp())->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException(); // avec l'en-tête Retry-After
        }
        
        // ... logique de l'endpoint
        return new JsonResponse($data);;
    }
}
```
