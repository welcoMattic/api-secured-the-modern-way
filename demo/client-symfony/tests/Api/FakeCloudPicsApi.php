<?php

namespace App\Tests\Api;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * CloudPics API, en test : le contrat qui compte, et rien d'autre.
 *
 * Branchée sur framework.http_client.mock_response_factory (voir
 * config/packages/test/framework.yaml), elle laisse le contrôleur, PhotoApiClient et
 * la vue intacts. Elle reproduit le seul comportement dont PhotoBook dépend : un
 * jeton dont l'audience n'est pas l'API se fait refuser en 401.
 */
final class FakeCloudPicsApi
{
    public const ACCESS_TOKEN = 'access-token-de-test';
    public const ID_TOKEN = 'id-token-de-test';
    public const SUB = '11111111-1111-4111-8111-111111111111';
    public const END_SESSION_ENDPOINT = 'https://localhost:8443/realms/photos/protocol/openid-connect/logout';

    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        if (str_ends_with($url, '/.well-known/openid-configuration')) {
            return new MockResponse(json_encode([
                'issuer' => 'https://localhost:8443/realms/photos',
                'authorization_endpoint' => 'https://localhost:8443/realms/photos/protocol/openid-connect/auth',
                'token_endpoint' => 'https://localhost:8443/realms/photos/protocol/openid-connect/token',
                'userinfo_endpoint' => 'https://localhost:8443/realms/photos/protocol/openid-connect/userinfo',
                'jwks_uri' => 'https://localhost:8443/realms/photos/protocol/openid-connect/certs',
                'end_session_endpoint' => self::END_SESSION_ENDPOINT,
            ], \JSON_THROW_ON_ERROR), [
                'http_code' => 200,
                'response_headers' => ['content-type' => 'application/json'],
            ]);
        }

        if (!str_ends_with($url, '/api/photos')) {
            return new MockResponse('Requête inattendue vers '.$url, ['http_code' => 501]);
        }

        // Le contre-exemple : l'ID token est valide, mais son audience est PhotoBook.
        if (self::ID_TOKEN === self::bearer($options)) {
            return new MockResponse('', [
                'http_code' => 401,
                'response_headers' => ['www-authenticate' => 'Bearer error="invalid_token",error_description="Invalid credentials."'],
            ]);
        }

        if ('POST' === $method) {
            return self::json(['@id' => '/api/photos/42', 'title' => 'Photo déposée par PhotoBook', 'owner' => self::SUB], 201);
        }

        return self::json([
            'totalItems' => 1,
            'member' => [['@id' => '/api/photos/1', 'title' => 'Coucher de soleil sur le Golden Gate', 'owner' => self::SUB]],
        ]);
    }

    private static function bearer(array $options): ?string
    {
        foreach ($options['headers'] ?? [] as $header) {
            if (str_starts_with((string) $header, 'Authorization: Bearer ')) {
                return substr((string) $header, \strlen('Authorization: Bearer '));
            }
        }

        return null;
    }

    private static function json(array $payload, int $status = 200): MockResponse
    {
        return new MockResponse(json_encode($payload, \JSON_THROW_ON_ERROR), [
            'http_code' => $status,
            'response_headers' => ['content-type' => 'application/ld+json'],
        ]);
    }
}
