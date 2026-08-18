<?php

namespace App\Controller;

use Drenso\OidcBundle\OidcClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(OidcClientInterface $oidcClient): Response
    {
        // On passe explicitement les scopes openid, profile et email.
        // Sans ça, le bundle ne demande que 'openid' par défaut,
        // et l'endpoint userinfo ne retourne pas l'email.
        return $oidcClient->generateAuthorizationRedirect(scopes: ['openid', 'profile', 'email']);
    }

    #[Route('/login_check', name: 'app_login_check')]
    public function loginCheck(): Response
    {
        // Cette action n'est JAMAIS atteinte.
        // L'authentificateur (OidcAuthenticator) intercepte la requête sur /login_check
        // dès qu'elle contient les paramètres 'code' et 'state', et gère tout le flow
        // (échange du code contre un token, création de la session, redirection).
        throw new \LogicException('app_login_check doit être interceptée par l\'authentificateur OIDC.');
    }

    /**
     * Oublie la session locale, sans passer par le endpoint de fin de session du Provider.
     *
     * La déconnexion normale envoie un id_token_hint. Si Keycloak a redémarré, ce token
     * a été signé par l'instance précédente : le Provider répond 400 et l'orateur se
     * retrouve devant une page d'erreur. Ici on jette simplement la session.
     */
    #[Route('/session/oublier', name: 'app_session_forget')]
    public function forgetSession(Request $request, TokenStorageInterface $tokenStorage): Response
    {
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        return $this->redirectToRoute('app_home');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        // Jamais atteinte non plus : le firewall intercepte /logout.
        // enable_end_session_listener: true déclenche aussi la déconnexion chez Keycloak.
        throw new \LogicException('app_logout doit être interceptée par le firewall.');
    }
}
