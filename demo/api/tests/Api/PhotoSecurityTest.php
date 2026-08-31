<?php

namespace App\Tests\Api;

use App\Tests\CloudPicsApiTestCase;
use App\Tests\Oidc\TestKeys;
use App\Tests\Oidc\TokenFactory;

/**
 * Ce que CloudPics API accepte, et ce qu'elle refuse.
 *
 * Chaque cas correspond à un geste de la démo. Le firewall, le token handler « oidc »
 * et sa discovery sont ceux de la configuration réelle : seules les réponses HTTP du
 * Provider sont simulées.
 */
final class PhotoSecurityTest extends CloudPicsApiTestCase
{
    public function testSansTokenLaCollectionRepond401(): void
    {
        static::createClient()->request('GET', '/api/photos');

        self::assertResponseStatusCodeSame(401);
    }

    /**
     * Le 500 d'hier : sans rôle vérifié d'abord, l'expression de sécurité de l'item
     * évaluait « object.owner == user.getUserIdentifier() » sur un user à null.
     */
    public function testSansTokenUnItemRepond401EtPas500(): void
    {
        static::createClient()->request('GET', '/api/photos/'.$this->photoBob);

        self::assertResponseStatusCodeSame(401);
    }

    public function testUnTokenQuiNEstPasUnJwtRepond401(): void
    {
        static::createClient()->request('GET', '/api/photos', ['auth_bearer' => 'not.a.jwt']);

        self::assertResponseStatusCodeSame(401);
        self::assertStringContainsString('invalid_token', $this->challenge());
    }

    /**
     * Le contre-exemple des deux apps : un jeton parfaitement valide, parfaitement
     * signé, mais dont l'audience est le client et non l'API.
     */
    public function testLIdTokenEstRefuseSurSonAudience(): void
    {
        static::createClient()->request('GET', '/api/photos', [
            'auth_bearer' => TokenFactory::idToken(TokenFactory::ALICE),
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    public function testUnTokenSigneParUnAutreEstRefuse(): void
    {
        static::createClient()->request('GET', '/api/photos', [
            'auth_bearer' => TokenFactory::accessToken(TokenFactory::ALICE, key: TestKeys::imposteur()),
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    public function testUnAutreIssuerEstRefuse(): void
    {
        static::createClient()->request('GET', '/api/photos', [
            'auth_bearer' => TokenFactory::accessToken(TokenFactory::ALICE, issuer: 'http://ailleurs.example/realms/photos'),
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    public function testUnTokenExpireEstRefuse(): void
    {
        static::createClient()->request('GET', '/api/photos', [
            'auth_bearer' => TokenFactory::accessToken(TokenFactory::ALICE, ttl: -60),
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    /**
     * Le cas qui prouve que l'intersection vit chez le Provider : le token est
     * authentique, mais le client n'a obtenu aucun scope photos, donc aucun rôle.
     * L'utilisateur est authentifié, et pourtant il ne peut rien lire.
     */
    public function testAuthentifieSansRoleRepond403(): void
    {
        static::createClient()->request('GET', '/api/photos', [
            'auth_bearer' => TokenFactory::accessToken(TokenFactory::ALICE, roles: []),
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testAliceNeVoitQueSesPhotos(): void
    {
        $response = static::createClient()->request('GET', '/api/photos', [
            'auth_bearer' => TokenFactory::accessToken(TokenFactory::ALICE),
        ]);

        self::assertResponseIsSuccessful();
        $owners = array_column($response->toArray()['member'], 'owner');
        self::assertSame([TokenFactory::ALICE, TokenFactory::ALICE], $owners);
    }

    public function testBobNeVoitQueSaPhoto(): void
    {
        $response = static::createClient()->request('GET', '/api/photos', [
            'auth_bearer' => TokenFactory::accessToken(TokenFactory::BOB, ['PHOTOS_READ']),
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame([TokenFactory::BOB], array_column($response->toArray()['member'], 'owner'));
    }

    public function testBobNAccedePasAUnePhotoDAlice(): void
    {
        static::createClient()->request('GET', '/api/photos/'.$this->photosAlice[0], [
            'auth_bearer' => TokenFactory::accessToken(TokenFactory::BOB, ['PHOTOS_READ']),
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testBobAccedeASaPhoto(): void
    {
        static::createClient()->request('GET', '/api/photos/'.$this->photoBob, [
            'auth_bearer' => TokenFactory::accessToken(TokenFactory::BOB, ['PHOTOS_READ']),
        ]);

        self::assertResponseIsSuccessful();
    }

    public function testBobNePeutPasDeposer(): void
    {
        static::createClient()->request('POST', '/api/photos', [
            'auth_bearer' => TokenFactory::accessToken(TokenFactory::BOB, ['PHOTOS_READ']),
            'headers' => ['Content-Type' => 'application/ld+json'],
            'body' => json_encode(['title' => 'Photo de bob', 'url' => 'https://cloudpics.example/bob.jpg']),
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testAlicePeutDeposerEtLeServeurImposeLeProprietaire(): void
    {
        $response = static::createClient()->request('POST', '/api/photos', [
            'auth_bearer' => TokenFactory::accessToken(TokenFactory::ALICE),
            'headers' => ['Content-Type' => 'application/ld+json'],
            'body' => json_encode([
                'title' => 'Photo déposée par le test',
                'url' => 'https://cloudpics.example/test.jpg',
                // Le client tente de se faire passer pour bob : la propriété n'est pas
                // écrivable, et le state processor écrase de toute façon.
                'owner' => TokenFactory::BOB,
            ]),
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertSame(TokenFactory::ALICE, $response->toArray()['owner']);
    }

    /**
     * Swagger UI doit rester lisible sans token, sinon le bouton « Authorize » est
     * derrière la porte qu'il est censé ouvrir.
     */
    public function testLaDocResteAccessibleSansToken(): void
    {
        static::createClient()->request('GET', '/api/docs', ['headers' => ['Accept' => 'text/html']]);

        self::assertResponseIsSuccessful();
    }

    /** L'en-tête qui porte la raison du refus, quand l'API en donne une. */
    private function challenge(): string
    {
        return self::getClient()->getResponse()->headers->get('www-authenticate') ?? '';
    }
}
