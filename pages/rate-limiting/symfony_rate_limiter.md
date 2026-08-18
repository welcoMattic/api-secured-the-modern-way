---
layout: default
class: sec-rate
---

# Symfony Rate Limiter

<v-clicks>

- 🛡️ Composant **intégré** à Symfony
- 🎯 Contre **brute force et DoS**
- 🔧 **3 stratégies** : fixed / sliding window, token bucket
- 📦 Depuis Symfony **5.3** (2021)

</v-clicks>

---
layout: default
class: sec-rate
---

# Configurer un limiter

<v-clicks>

- 📝 Dans `config/packages/rate_limiter.yaml`
- 🔢 Limite, intervalle, stockage
- 🎯 Un limiter **par usage**

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
class: sec-rate
---

# API Platform n'a pas de contrôleur à décorer

<v-clicks>

- 🧩 Un **state provider** décoré : la limite s'applique **par opération**
- 🏷️ Déclaré sur l'opération, exactement comme `security:`
- 🛡️ `TooManyRequestsHttpException` → **HTTP 429** automatique
- 🌐 Pour couvrir **toute l'API** d'un coup : un listener `kernel.request`

</v-clicks>

---
layout: default
class: sec-rate
---

# Le provider consomme un jeton, puis délègue

```php
// src/State/RateLimitedProvider.php
final class RateLimitedProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $inner,
        private RateLimiterFactoryInterface $apiLimiter,
    ) {}

    public function provide(Operation $op, array $uriVariables = [], array $context = []): object|array|null {
        $request = $context['request'] ?? null;
        $limit = $this->apiLimiter->create($request?->getClientIp())->consume();
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp() - time());
        }
        $request?->attributes->set('rate_limit', $limit);

        return $this->inner->provide($op, $uriVariables, $context);
    }
}
```

---
layout: default
class: sec-rate
---

# On branche la limite sur l'opération

```php
// src/Entity/Photo.php
#[ApiResource]
#[GetCollection(provider: RateLimitedProvider::class)]
#[Post(security: "is_granted('ROLE_PHOTOS_WRITE')")]
class Photo
{
    // ...
}
```

<v-click>

#### Une opération limitée, l'autre non. 
#### Le même style déclaratif que `security:`. {.mt-6}

</v-click>

---
layout: default
class: sec-rate
---

# Renvoyer les quotas au client

```php
// src/EventListener/RateLimitHeadersListener.php
#[AsEventListener(KernelEvents::RESPONSE)]
final class RateLimitHeadersListener
{
    public function __invoke(ResponseEvent $event): void
    {
        if (!$limit = $event->getRequest()->attributes->get('rate_limit')) {
            return;
        }

        $event->getResponse()->headers->add([
            'X-RateLimit-Limit' => $limit->getLimit(),
            'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
        ]);
    }
}
```

<v-click>

Les bons clients lisent ces en-têtes et **ralentissent** avant le 429. {.opacity-70}

</v-click>

---
layout: default
class: sec-rate
---

# En prod : stockage partagé

<v-clicks>

- ⚠️ Défaut : **cache local** (`cache.rate_limiter`)
- 🤹 N instances = **N compteurs** → limite multipliée
- ✅ **Redis** pour partager

</v-clicks>

<div v-click>

```yaml
# config/packages/cache.yaml
framework:
    cache:
        pools:
            cache.rate_limiter.redis:
                adapter: cache.adapter.redis
                provider: 'redis://localhost'
    rate_limiter:
        api:
            # ...
            cache_pool: 'cache.rate_limiter.redis'
```

</div>
