<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

/**
 * API Platform documente déjà les schémas de sécurité (section oauth + swagger.http_auth)
 * et la réponse 403 des opérations protégées. Ce décorateur comble ce qui manque encore
 * à un générateur de clients : la réponse 401, les scopes de l'exigence de sécurité
 * globale, et une description utile des deux schémas.
 */
#[AsDecorator('api_platform.openapi.factory')]
final readonly class SecurityDocumentationFactory implements OpenApiFactoryInterface
{
    /**
     * Le 401 reprend les mêmes schémas d'erreur que le 403 généré par API Platform :
     * une seule forme d'erreur dans toute la spec, donc un seul type côté client généré.
     */
    private const UNAUTHORIZED_CONTENT = [
        'application/ld+json' => ['schema' => ['$ref' => '#/components/schemas/Error.jsonld']],
        'application/problem+json' => ['schema' => ['$ref' => '#/components/schemas/Error']],
        'application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']],
    ];

    /**
     * Les descriptions générées ("Value for the http bearer parameter.") ne disent pas
     * d'où sort un token. Celles-ci nomment le Provider et la façon de vérifier.
     */
    private const SCHEME_DESCRIPTIONS = [
        'oauth' => "OAuth 2.0 authorization_code + PKCE auprès de CloudPics ID. C'est le seul chemin pour obtenir un token : CloudPics API n'en délivre aucun.",
        'bearer' => "Access token JWT déjà obtenu, envoyé tel quel dans l'en-tête Authorization. Vérifié hors ligne : signature RS256, issuer et audience.",
    ];

    public function __construct(private OpenApiFactoryInterface $decorated)
    {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $paths = $openApi->getPaths();

        foreach ($paths->getPaths() as $path => $pathItem) {
            $paths->addPath($path, $this->documentPathItem($pathItem));
        }

        return $this->describeSecuritySchemes($openApi)->withSecurity($this->claimOauthScopes($openApi));
    }

    /**
     * Toutes les opérations de ce path passent par le firewall : toutes peuvent répondre 401.
     */
    private function documentPathItem(PathItem $pathItem): PathItem
    {
        foreach (['Get', 'Post', 'Put', 'Patch', 'Delete'] as $method) {
            $operation = $pathItem->{'get'.$method}();

            if ($operation instanceof Operation) {
                $pathItem = $pathItem->{'with'.$method}($this->documentOperation($operation, $method));
            }
        }

        return $pathItem;
    }

    private function documentOperation(Operation $operation, string $method): Operation
    {
        if (!isset(($operation->getResponses() ?? [])['401'])) {
            $operation = $operation->withResponse('401', new Response(
                'Access token absent, expiré, ou signature invalide',
                new \ArrayObject(self::UNAUTHORIZED_CONTENT),
            ));
        }

        return $operation->withDescription(trim(
            ($operation->getDescription() ?? '')."\n\n".$this->describeRequiredRoles($method)
        ));
    }

    /**
     * Ces rôles ne sont pas des scopes OAuth2 : ils voyagent dans le claim
     * realm_access.roles de l'access token, et OidcUserProvider les mappe vers ROLE_*.
     * Un client ne peut donc pas les demander, il peut seulement les recevoir.
     */
    private function describeRequiredRoles(string $method): string
    {
        return 'Post' === $method
            ? 'Rôles requis : `ROLE_USER` et `ROLE_PHOTOS_WRITE`, mappés depuis le claim `realm_access.roles` de l\'access token.'
            : 'Rôle requis : `ROLE_USER`, mappé depuis le claim `realm_access.roles` de l\'access token.';
    }

    private function describeSecuritySchemes(OpenApi $openApi): OpenApi
    {
        $components = $openApi->getComponents();
        $schemes = $components->getSecuritySchemes() ?? new \ArrayObject();

        foreach (self::SCHEME_DESCRIPTIONS as $name => $description) {
            if ($schemes[$name] ?? null) {
                $schemes[$name] = $schemes[$name]->withDescription($description);
            }
        }

        return $openApi->withComponents($components->withSecuritySchemes($schemes));
    }

    /**
     * API Platform déclare l'exigence globale sans aucun scope. Sans cette étape,
     * un client généré construirait une URL d'autorisation sans `openid`, et Keycloak
     * refuserait de faire de l'OIDC.
     *
     * @return list<array<string, list<string>>>
     */
    private function claimOauthScopes(OpenApi $openApi): array
    {
        $schemes = $openApi->getComponents()->getSecuritySchemes() ?? new \ArrayObject();

        return array_map(static function (array $requirement) use ($schemes): array {
            foreach (array_keys($requirement) as $name) {
                $scheme = $schemes[$name] ?? null;
                $flow = $scheme instanceof SecurityScheme ? $scheme->getFlows()?->getAuthorizationCode() : null;

                if (null !== $flow) {
                    $requirement[$name] = array_keys($flow->getScopes()->getArrayCopy());
                }
            }

            return $requirement;
        }, $openApi->getSecurity());
    }
}
