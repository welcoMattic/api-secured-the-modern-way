---
layout: default
class: sec-rate
---

# Symfony Rate Limiter: just configuration

<v-clicks>

- 🎯 Against **abuse**: brute force, scraping, overly greedy clients
- 🔧 A limit, an interval, a storage. One limiter **per use case**

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

<v-click>

<div class="slide-note">In production, N instances = N counters: use a <b>shared</b> cache pool (Redis) through <code>cache_pool</code>.</div>

</v-click>

---
layout: default
class: sec-rate
---

# The limit is declared on the operation

<v-clicks>

- 🧩 A decorated **state provider**: the limit applies **per operation**
- 🛡️ `TooManyRequestsHttpException` → automatic **HTTP 429**
- 🔑 Quota key: the token's `sub`, not the IP

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

<div class="slide-punch">One operation limited, the other not.<br/>The same declarative style as <code>security:</code></div>

</v-click>

---
layout: default
class: sec-rate
---

# The provider consumes a token, then delegates

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
        $key = $this->security->getUser()?->getUserIdentifier(); // the token's sub
        $limit = $this->apiLimiter->create($key)->consume();
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp() - time());
        }

        return $this->inner->provide($op, $uriVariables, $context);
    }
}
```


