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
     * déclenche avant ce contrôleur. L'authenticator oidc_login est ce point d'entrée,
     * et il part directement chez CloudPics ID. On n'arrive ici qu'une fois authentifié,
     * quand le success handler rejoue l'URL demandée.
     */
    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    /**
     * Oublie la session locale depuis un lien, sans passer par le firewall.
     *
     * Sert au rattrapage du 401 affiché sur la page d'accueil : quand CloudPics ID a
     * redémarré, les tokens en session ne sont plus vérifiables et la seule issue est
     * de tout jeter avant de relancer le flow.
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
        // Il ferme la session de PhotoBook seulement : la PR mergée dans le Core ne
        // couvre pas le RP-Initiated Logout, la session CloudPics ID reste ouverte.
        throw new \LogicException('app_logout doit être interceptée par le firewall.');
    }
}
