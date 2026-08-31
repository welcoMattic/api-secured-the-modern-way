<?php

namespace App\Tests\Oidc;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;

/**
 * Fabrique les tokens que CloudPics ID émettrait.
 *
 * OidcTokenGenerator, fourni par Symfony, ne sait poser que le claim « sub » : il ne
 * peut donc pas produire le realm_access.roles dont dépend toute l'autorisation de
 * l'API. D'où ces vingt lignes, qui donnent la main sur chaque claim, y compris ceux
 * qu'on veut voir refuser.
 */
final class TokenFactory
{
    public const ALICE = '11111111-1111-4111-8111-111111111111';
    public const BOB = '22222222-2222-4222-8222-222222222222';
    public const AUDIENCE = 'cloudpics-api';

    /**
     * Un access token tel que Keycloak le signe pour l'API.
     *
     * @param list<string> $roles Le claim realm_access.roles, c'est-à-dire l'intersection
     *                            que le Provider a déjà calculée entre les rôles du compte
     *                            et les scopes accordés au client
     */
    public static function accessToken(
        string $sub,
        array $roles = ['PHOTOS_READ', 'PHOTOS_WRITE'],
        string $audience = self::AUDIENCE,
        string $issuer = FakeCloudPicsId::ISSUER,
        ?JWK $key = null,
        int $ttl = 300,
    ): string {
        $now = time();
        $scopes = array_map(
            static fn (string $role) => 'PHOTOS_READ' === $role ? 'photos:read' : 'photos:write',
            $roles,
        );

        return self::sign([
            'sub' => $sub,
            'aud' => $audience,
            'iss' => $issuer,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttl,
            'azp' => 'photoprint',
            'preferred_username' => self::ALICE === $sub ? 'alice' : 'bob',
            'email' => (self::ALICE === $sub ? 'alice' : 'bob').'@example.com',
            'scope' => implode(' ', ['openid', 'profile', 'email', ...$scopes]),
            'realm_access' => ['roles' => $roles],
        ], $key ?? TestKeys::provider());
    }

    /**
     * L'ID token du contre-exemple : même signature, même issuer, audience du client.
     *
     * C'est le jeton que les deux apps envoient exprès pour récolter un 401.
     */
    public static function idToken(string $sub): string
    {
        return self::accessToken($sub, audience: 'photoprint');
    }

    /**
     * @param array<string, mixed> $claims
     */
    private static function sign(array $claims, JWK $key): string
    {
        $jws = (new JWSBuilder(new AlgorithmManager([new RS256()])))
            ->create()
            ->withPayload(json_encode($claims, \JSON_THROW_ON_ERROR))
            ->addSignature($key, ['alg' => 'RS256', 'kid' => $key->get('kid')])
            ->build();

        return (new CompactSerializer())->serialize($jws, 0);
    }
}
