---
layout: default
class: sec-rate
---

# Symfony Rate Limiter : une simple config

<v-clicks>

- 🎯 Contre les **abus** : brute force, scraping, clients trop gourmands
- 🔧 Une limite, un intervalle, un stockage. Un limiter **par usage**

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

# La limite se déclare sur l'opération

<v-clicks>

- 🧩 Un **state provider** décoré : la limite s'applique **par opération**
- 🛡️ `TooManyRequestsHttpException` → **HTTP 429** automatique
- 🔑 Clé du quota : le `sub` du token, pas l'IP

</v-clicks>

<div v-click>

```php
// src/Entity/Photo.php
#[ApiResource(security: "is_granted('ROLE_PHOTOS_READ')")]
#[GetCollection(provider: RateLimitedProvider::class)]
#[Post(security: "is_granted('ROLE_PHOTOS_WRITE')")]
class Photo
{
    // ...
}
```

</div>

<v-click>

<div class="slide-punch">Une opération limitée, l'autre non.<br/>Le même style déclaratif que <code>security:</code></div>

</v-click>

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
        private Security $security,
    ) {}

    public function provide(Operation $op, array $uriVariables = [], array $context = []): object|array|null {
        $key = $this->security->getUser()?->getUserIdentifier(); // le sub du token
        $limit = $this->apiLimiter->create($key)->consume();
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp() - time());
        }

        return $this->inner->provide($op, $uriVariables, $context);
    }
}
```

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
