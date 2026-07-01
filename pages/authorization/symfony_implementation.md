---
layout: default
---

# Implémentation d'OAuth2 dans Symfony

<v-clicks>

- 📦 **Bundle** : `league/oauth2-server-bundle`
- 🔧 **Intégration** : le bundle Symfony officiel du serveur OAuth2 de League
- 🛡️ **Résultat** : **embarquer un authorization server OAuth2 moderne** dans votre API Symfony
- ⚡ **En pratique** : 
  - Votre API est le **resource server**
  - Le bundle gère la validation des tokens et la vérification des scopes pour les endpoints protégés

</v-clicks>

---
layout: default
---

# Installation du bundle

<v-clicks>

```bash
composer require league/oauth2-server-bundle
```

</v-clicks>

---
layout: default
---

# Configuration minimale

```yaml
# config/packages/league_oauth2_server.yaml
league_oauth2_server:
    authorization_server:
        private_key: '%kernel.project_dir%/var/oauth/private.key'
        encryption_key: 'def00000examplekey1234567890ab'
    
    resource_server:
        public_key: '%kernel.project_dir%/var/oauth/public.key'
```

<div class="mb-4"></div>

<div class="grid grid-cols-3 gap-4" v-click>

```yaml
# config/packages/security.yaml
security:
    firewalls:
        api_token:
            pattern: ^/token$
            security: false 
        main:
            pattern: ^/api
            security: true
            stateless: true
            oauth2: true
```

<div class="col-span-2" v-click="2">

```yaml
# config/routes.yaml
oauth2:
    resource: '@LeagueOAuth2ServerBundle/config/routes.php'
    type: php
```

</div>

</div>

---
layout: default
---

# Entité Client

```php
use League\OAuth2\Server\Entities;

#[ORM\Entity]
class Client implements ClientEntityInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 80)]
    private string $identifier;
    
    #[ORM\Column(length: 80)]
    private string $secret;
    
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
```

---
layout: default
---

# Entité Access token

```php
use League\OAuth2\Server\Entities;

#[ORM\Entity]
class AccessToken implements AccessTokenEntityInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    
    #[ORM\ManyToOne(targetEntity: Client::class)]
    private ClientEntityInterface $client;
    
    #[ORM\Column(type: 'json')]
    private array $scopes = [];
    
    public function getClient(): ClientEntityInterface
    {
        return $this->client;
    }
}
```

---
layout: default
---

# Endpoint d'API protégé

```php
// src/Controller/ApiController.php
class ApiController extends AbstractController
{
    #[Route('/api/protected')]
    #[IsGranted('ROLE_OAUTH2_<scope>')]
    public function protectedEndpoint(): JsonResponse
    {
        return $this->json(['data' => 'Protected resource']);
    }
}
```
