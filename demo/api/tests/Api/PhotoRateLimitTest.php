<?php

namespace App\Tests\Api;

use App\Tests\CloudPicsApiTestCase;
use App\Tests\Oidc\TokenFactory;

/**
 * Tests du rate limiting sur la collection de photos.
 */
final class PhotoRateLimitTest extends CloudPicsApiTestCase
{
    /**
     * Un seul client, quatre GET /api/photos avec le token d'alice :
     * les trois premiers répondent 200, le quatrième 429.
     */
    public function testAuDelaDuQuotaLaCollectionRepond429(): void
    {
        $client = static::createClient();
        $token = TokenFactory::accessToken(TokenFactory::ALICE);

        // Trois requêtes acceptées
        for ($i = 0; $i < 3; $i++) {
            $client->request('GET', '/api/photos', ['auth_bearer' => $token]);
            self::assertResponseIsSuccessful();
        }

        // Quatrième requête : 429
        $client->request('GET', '/api/photos', ['auth_bearer' => $token]);
        self::assertResponseStatusCodeSame(429);

        // Vérification de la présence de l'en-tête Retry-After
        $retryAfter = self::getClient()->getResponse()->headers->get('Retry-After');
        // On ne fait que vérifier sa présence, pas sa valeur
        $this->assertNotNull($retryAfter, 'L\'en-tête Retry-After est présent sur la réponse 429.');
    }

    /**
     * La clé du seau est le sub du token, pas l'adresse IP, qui est la même
     * pour tout le monde dans ces tests.
     */
    public function testLeQuotaEstParUtilisateurEtPasParIp(): void
    {
        $client = static::createClient();
        $aliceToken = TokenFactory::accessToken(TokenFactory::ALICE);
        $bobToken = TokenFactory::accessToken(TokenFactory::BOB);

        // Alice épuise son quota (3 requêtes OK, 4e en 429)
        for ($i = 0; $i < 3; $i++) {
            $client->request('GET', '/api/photos', ['auth_bearer' => $aliceToken]);
            self::assertResponseIsSuccessful();
        }
        $client->request('GET', '/api/photos', ['auth_bearer' => $aliceToken]);
        self::assertResponseStatusCodeSame(429);

        // Bob, avec son propre token, a encore son quota intact
        $client->request('GET', '/api/photos', ['auth_bearer' => $bobToken]);
        self::assertResponseIsSuccessful();
    }
}
