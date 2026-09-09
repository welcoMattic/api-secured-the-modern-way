<?php

namespace App\Tests\Oidc;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Le Provider, en test : deux réponses HTTP et rien d'autre.
 *
 * Le token handler « oidc » découvre les clés de signature par HTTP, comme en vrai.
 * On ne remplace donc aucun service de sécurité : on branche cette fabrique sur
 * framework.http_client.mock_response_factory (voir config/packages/test/framework.yaml),
 * ce qui intercepte le client de discovery sans toucher au firewall. Les tests
 * exercent exactement la configuration de production.
 */
final class FakeCloudPicsId
{
    public const ISSUER = 'https://localhost:8443/realms/photos';
    public const JWKS_URI = self::ISSUER.'/protocol/openid-connect/certs';

    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        if (str_ends_with($url, '/.well-known/openid-configuration')) {
            return new MockResponse(json_encode([
                'issuer' => self::ISSUER,
                // Absolu : le handler enchaîne une deuxième requête sur cette URL.
                'jwks_uri' => self::JWKS_URI,
            ], \JSON_THROW_ON_ERROR), ['response_headers' => ['content-type' => 'application/json']]);
        }

        if (self::JWKS_URI === $url) {
            return new MockResponse(json_encode(TestKeys::jwks(), \JSON_THROW_ON_ERROR), [
                'response_headers' => ['content-type' => 'application/json'],
            ]);
        }

        // Un appel sortant non prévu doit casser le test, pas le rendre vert par hasard.
        return new MockResponse('Requête inattendue vers '.$url, ['http_code' => 501]);
    }
}
