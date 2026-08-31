<?php

namespace App\Tests\Controller;

use App\Tests\Api\FakeCloudPicsApi;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\OidcUser;

/**
 * Les trois boutons de PhotoBook, du clic à l'affichage.
 *
 * Le firewall est celui de la démo : l'authenticator natif « oidc_login », le provider
 * natif « oidc ». loginUser() pose la même chose que lui, y compris les deux tokens
 * dans les attributs du token de sécurité, là où PhotoApiClient va les chercher.
 */
final class PhotoControllerTest extends WebTestCase
{
    public function testLaPageAnonymeProposeSeulementDeSeConnecter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Se connecter avec CloudPics ID');
    }

    public function testLaPageConnecteeAfficheLIdentiteVenueDuProvider(): void
    {
        $client = $this->connecte();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.user-pill', 'alice');
        self::assertSelectorTextContains('.claims', FakeCloudPicsApi::SUB);
    }

    /**
     * PhotoBook ne reçoit que ROLE_USER : ce n'est pas lui qui décide des permissions,
     * et la page le dit à la salle.
     */
    public function testLaPageConnecteeRappelleQuePhotoBookNaQueRoleUser(): void
    {
        $client = $this->connecte();
        $client->request('GET', '/');

        self::assertSelectorTextContains('.card-note', 'ROLE_USER');
    }

    public function testLeGetAfficheLaReponseDeLApi(): void
    {
        $client = $this->connecte();
        $client->request('POST', '/photos/list');

        self::assertResponseRedirects('/');
        $crawler = $client->followRedirect();

        self::assertSelectorTextContains('.status-chip', '200');
        self::assertStringContainsString('Golden Gate', $crawler->filter('.response-body')->text());
    }

    public function testLePostAfficheUn201(): void
    {
        $client = $this->connecte();
        $client->request('POST', '/photos/create');
        $client->followRedirect();

        self::assertSelectorTextContains('.status-chip', '201');
    }

    /**
     * Le contre-exemple, de bout en bout : l'ID token part vers l'API, l'API le refuse
     * sur son audience, et la vue explique le refus au lieu de proposer d'oublier la
     * session. Confondre les deux 401 ferait dire à la démo l'inverse du propos.
     */
    public function testLeContreExempleExpliqueLAudienceEtNePasProposeDOublierLaSession(): void
    {
        $client = $this->connecte();
        $client->request('POST', '/photos/id-token');
        $crawler = $client->followRedirect();

        self::assertSelectorTextContains('.status-chip', '401');
        self::assertSelectorTextContains('.response-challenge', 'invalid_token');
        self::assertStringContainsString('audience', $crawler->filter('.response-hint')->text());
        self::assertSame(0, $crawler->filter('.response-hint a')->count());
    }

    private function connecte(): KernelBrowser
    {
        $client = static::createClient();

        $client->loginUser(
            new OidcUser(
                userIdentifier: FakeCloudPicsApi::SUB,
                sub: FakeCloudPicsApi::SUB,
                preferredUsername: 'alice',
                email: 'alice@example.com',
            ),
            'main',
            // L'authenticator natif pose les deux tokens comme attributs du token de
            // sécurité : c'est ce qui leur fait survivre à la session.
            [
                'oidc_access_token' => FakeCloudPicsApi::ACCESS_TOKEN,
                'oidc_id_token' => FakeCloudPicsApi::ID_TOKEN,
            ],
        );

        return $client;
    }
}
