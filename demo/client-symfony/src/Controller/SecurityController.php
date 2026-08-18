<?php

namespace App\Controller;

use Drenso\OidcBundle\OidcClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // La déconnexion est gérée par le firewall (oidc + logout).
        // enable_end_session_listener: true déclenche aussi la déconnexion chez Keycloak.
    }
}
