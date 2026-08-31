<?php

namespace App\Tests\Oidc;

use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;

/**
 * La paire de clés RS256 du faux CloudPics ID.
 *
 * Elle est générée une fois par processus PHPUnit, jamais commitée : un dépôt public
 * n'a pas à porter de clé privée, même de test. La clé publique est servie par
 * FakeCloudPicsId au token handler, la privée signe les tokens dans TokenFactory.
 *
 * L'autre paire, elle, existe pour un seul scénario : un token parfaitement formé mais
 * signé par quelqu'un d'autre, que l'API doit refuser.
 */
final class TestKeys
{
    private static ?JWK $provider = null;
    private static ?JWK $imposteur = null;

    public static function provider(): JWK
    {
        return self::$provider ??= self::generate('cloudpics-id-test');
    }

    public static function imposteur(): JWK
    {
        return self::$imposteur ??= self::generate('imposteur-test');
    }

    /**
     * Le JWKS que le faux Provider expose, tel que Keycloak l'exposerait.
     *
     * @return array{keys: list<array<string, mixed>>}
     */
    public static function jwks(): array
    {
        return ['keys' => [self::provider()->toPublic()->all()]];
    }

    private static function generate(string $kid): JWK
    {
        // 2048 bits : le minimum sérieux pour RS256, et assez rapide pour une suite de tests.
        return JWKFactory::createRSAKey(2048, [
            'alg' => 'RS256',
            'use' => 'sig',
            'kid' => $kid,
        ]);
    }
}
