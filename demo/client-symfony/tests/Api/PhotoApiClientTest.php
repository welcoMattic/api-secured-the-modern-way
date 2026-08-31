<?php

namespace App\Tests\Api;

use App\Api\PhotoApiClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\User\OidcUser;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Ce que PhotoBook relaie vers CloudPics API, et ce qu'il en montre.
 *
 * PhotoBook ne décide d'aucune permission : il transporte un token et affiche une
 * réponse. Ces tests fixent les deux seuls endroits où il pourrait mentir : le jeton
 * qu'il envoie, et le corps qu'il projette.
 */
final class PhotoApiClientTest extends TestCase
{
    private const ACCESS_TOKEN = 'access-token-de-la-session';
    private const ID_TOKEN = 'id-token-de-la-session';

    public function testListRelaieLAccessTokenDeLaSession(): void
    {
        $vues = [];
        $client = $this->client($this->espion($vues, body: '{"member":[]}'));

        $client->list();

        self::assertSame('GET', $vues[0]['method']);
        self::assertSame('http://api.test/api/photos', $vues[0]['url']);
        self::assertContains('Authorization: Bearer '.self::ACCESS_TOKEN, $vues[0]['headers']);
    }

    public function testCreateEnvoieUnCorpsJsonLdSigneParLAccessToken(): void
    {
        $vues = [];
        $client = $this->client($this->espion($vues, status: 201, body: '{"owner":"alice"}'));

        $client->create('Une photo', 'https://cloudpics.example/photo.jpg');

        self::assertSame('POST', $vues[0]['method']);
        self::assertContains('Authorization: Bearer '.self::ACCESS_TOKEN, $vues[0]['headers']);
        self::assertContains('Content-Type: application/ld+json', $vues[0]['headers']);
        self::assertSame(
            ['title' => 'Une photo', 'url' => 'https://cloudpics.example/photo.jpg'],
            json_decode($vues[0]['body'], true),
        );
    }

    /**
     * Le contre-exemple de la démo : le même appel, avec l'ID token. C'est bien l'ID
     * token qui part sur le réseau, et c'est l'API qui le refusera.
     */
    public function testListWithIdTokenEnvoieLIdToken(): void
    {
        $vues = [];
        $client = $this->client($this->espion($vues, status: 401, body: ''));

        $resultat = $client->listWithIdToken();

        self::assertContains('Authorization: Bearer '.self::ID_TOKEN, $vues[0]['headers']);
        self::assertSame(401, $resultat['status']);
    }

    public function testLaTracePhpEstRetireeDuCorpsProjete(): void
    {
        $erreur = json_encode([
            'detail' => 'Access Denied.',
            'trace' => [['file' => '/vendor/symfony/.../Firewall.php', 'line' => 42]],
        ]);
        $client = $this->client(new MockHttpClient(new MockResponse($erreur, ['http_code' => 403])));

        $resultat = $client->list();

        self::assertSame(403, $resultat['status']);
        self::assertStringContainsString('Access Denied.', $resultat['body']);
        self::assertStringNotContainsString('trace', $resultat['body']);
    }

    public function testLeChallengeDuA401EstRemonte(): void
    {
        $client = $this->client(new MockHttpClient(new MockResponse('', [
            'http_code' => 401,
            'response_headers' => ['www-authenticate' => 'Bearer error="invalid_token"'],
        ])));

        $resultat = $client->list();

        self::assertSame('Bearer error="invalid_token"', $resultat['challenge']);
        // Un 401 n'a pas de corps : la vue doit quand même avoir quelque chose à afficher.
        self::assertSame('(corps vide)', $resultat['body']);
    }

    /**
     * Sans ce filet, une API arrêtée projetterait une page d'erreur 500 de dev,
     * stack trace comprise, devant la salle.
     */
    public function testUneApiInjoignableNeLeveAucuneException(): void
    {
        $client = $this->client(new MockHttpClient(static function (): never {
            throw new \Symfony\Component\HttpClient\Exception\TransportException('Connection refused');
        }));

        $resultat = $client->list();

        self::assertSame(0, $resultat['status']);
        self::assertStringContainsString('http://api.test', $resultat['body']);
        self::assertNull($resultat['challenge']);
    }

    /**
     * Les tokens vivent dans les attributs du token de sécurité. S'ils manquent, la
     * session n'a pas été ouverte par l'authenticator : mieux vaut le dire.
     */
    public function testSansTokenDansLaSessionLAppelEchoueExplicitement(): void
    {
        $client = new PhotoApiClient(new MockHttpClient(), new TokenStorage(), 'http://api.test');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('oidc_access_token');
        $client->list();
    }

    /**
     * Capture ce qui partirait vraiment sur le réseau.
     *
     * @param array<int, array{method: string, url: string, headers: list<string>, body: string}> $vues
     */
    private function espion(array &$vues, int $status = 200, string $body = '{}'): MockHttpClient
    {
        return new MockHttpClient(static function (string $method, string $url, array $options) use (&$vues, $status, $body): ResponseInterface {
            $vues[] = [
                'method' => $method,
                'url' => $url,
                'headers' => $options['headers'] ?? [],
                'body' => \is_string($options['body'] ?? null) ? $options['body'] : '',
            ];

            return new MockResponse($body, ['http_code' => $status]);
        });
    }

    private function client(MockHttpClient $http): PhotoApiClient
    {
        $user = new OidcUser(userIdentifier: 'alice-sub', sub: 'alice-sub', preferredUsername: 'alice');
        $token = new PostAuthenticationToken($user, 'main', $user->getRoles());
        $token->setAttributes([
            'oidc_access_token' => self::ACCESS_TOKEN,
            'oidc_id_token' => self::ID_TOKEN,
        ]);

        $storage = new TokenStorage();
        $storage->setToken($token);

        return new PhotoApiClient($http, $storage, 'http://api.test');
    }
}
