<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    /**
     * Ne rend aucune page : /login est protégé, donc le point d'entrée du firewall se
     * déclenche avant ce contrôleur et part chez CloudPics ID (direct_redirect: true).
     * On n'arrive ici qu'une fois authentifié, quand le success handler rejoue l'URL
     * demandée.
     */
    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->redirectToRoute('app_home');
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
        // enable_end_session: true déclenche aussi la déconnexion chez CloudPics ID.
        throw new \LogicException('app_logout doit être interceptée par le firewall.');
    }
}
