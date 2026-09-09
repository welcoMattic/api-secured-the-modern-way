<?php

namespace App\Tests\Controller;

use App\Tests\Api\FakeCloudPicsApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\OidcUser;

/**
 * Les deux façons de quitter PhotoBook.
 *
 * « Se déconnecter » passe par le firewall, « Oublier la session » par ce contrôleur.
 * Le RP-Initiated Logout est mergé dans 8.2 et activé : « Se déconnecter » ferme aussi
 * la session CloudPics ID, « Oublier la session » reste local.
 */
final class SecurityControllerTest extends WebTestCase
{
    public function testOublierLaSessionDeconnecteSansParlerAuProvider(): void
    {
        $client = static::createClient();
        $client->loginUser($this->alice(), 'main', [
            'oidc_access_token' => FakeCloudPicsApi::ACCESS_TOKEN,
            'oidc_id_token' => FakeCloudPicsApi::ID_TOKEN,
        ]);

        $client->request('GET', '/session/oublier');

        self::assertResponseRedirects('/');
        $client->followRedirect();
        // Retour à la page d'accueil anonyme : plus d'identité, donc plus de session.
        self::assertSelectorTextContains('body', 'Se connecter avec CloudPics ID');
    }

    /**
     * « Oublier la session » doit rester atteignable même sans session valide : c'est
     * exactement la situation où l'on en a besoin.
     */
    public function testOublierLaSessionEstAccessibleAnonymement(): void
    {
        $client = static::createClient();
        $client->request('GET', '/session/oublier');

        self::assertResponseRedirects('/');
    }

    public function testSeDeconnecterFermeLaSessionChezLeProvider(): void
    {
        $client = static::createClient();
        $client->loginUser($this->alice(), 'main', [
            'oidc_access_token' => FakeCloudPicsApi::ACCESS_TOKEN,
            'oidc_id_token' => FakeCloudPicsApi::ID_TOKEN,
        ]);

        $client->request('GET', '/logout');

        $response = $client->getResponse();
        self::assertSame(302, $response->getStatusCode());

        $location = $response->headers->get('location');
        self::assertStringContainsString(FakeCloudPicsApi::END_SESSION_ENDPOINT, $location);
        self::assertStringContainsString('id_token_hint='.FakeCloudPicsApi::ID_TOKEN, $location);
        self::assertStringContainsString('post_logout_redirect_uri=http%3A%2F%2Flocalhost%2F', $location);

        // Vérifions que la session locale est bien fermée
        $client->request('GET', '/');
        self::assertSelectorTextContains('body', 'Se connecter avec CloudPics ID');
    }

    /**
     * /login n'est pas public : son refus déclenche le point d'entrée du firewall, qui
     * est l'authenticator lui-même et part droit chez CloudPics ID sans page
     * intermédiaire. Sans la route du check_path, le retour du Provider tomberait sur
     * un 404 du routeur.
     */
    public function testLaRouteDuCheckPathEstDeclaree(): void
    {
        self::bootKernel();
        $routes = self::getContainer()->get('router')->getRouteCollection();

        $route = $routes->get('_oidc_login_callback_main');

        self::assertNotNull($route, 'La route du check_path oidc_login est absente du routeur.');
        self::assertSame('/login_check', $route->getPath());

        $route = $routes->get('_oidc_login_start_main');

        self::assertNotNull($route, 'La route du start_path oidc_login est absente du routeur.');
        self::assertSame('/oidc/start', $route->getPath());
    }

    private function alice(): OidcUser
    {
        return new OidcUser(
            userIdentifier: FakeCloudPicsApi::SUB,
            sub: FakeCloudPicsApi::SUB,
            preferredUsername: 'alice',
        );
    }
}
