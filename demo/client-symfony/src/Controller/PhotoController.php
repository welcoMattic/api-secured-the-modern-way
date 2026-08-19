<?php

namespace App\Controller;

use App\Api\PhotoApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PhotoController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('home.html.twig');
    }

    #[Route('/photos/list', name: 'app_photos_list', methods: ['POST'])]
    public function list(PhotoApiClient $photoApiClient, Request $request): Response
    {
        return $this->flasher($request, $photoApiClient->list());
    }

    /**
     * Le contre-exemple : le même GET, avec l'ID token à la place de l'access token.
     * Il doit échouer, et c'est tout l'intérêt.
     */
    #[Route('/photos/id-token', name: 'app_photos_id_token', methods: ['POST'])]
    public function withIdToken(PhotoApiClient $photoApiClient, Request $request): Response
    {
        return $this->flasher($request, $photoApiClient->listWithIdToken(), counterExample: true);
    }

    #[Route('/photos/create', name: 'app_photos_create', methods: ['POST'])]
    public function create(PhotoApiClient $photoApiClient, Request $request): Response
    {
        return $this->flasher($request, $photoApiClient->create('Photo déposée par PhotoBook', 'https://cloudpics.example/album.jpg'));
    }

    /**
     * @param array{status: int, body: string, challenge: ?string} $result
     *
     * `counterExample` distingue les deux façons de récolter un 401. Sur le
     * contre-exemple, le refus est la démonstration : la vue ne doit surtout pas
     * conseiller d'oublier la session.
     */
    private function flasher(Request $request, array $result, bool $counterExample = false): Response
    {
        $request->getSession()->getFlashBag()->add('api_result', [
            'status' => $result['status'],
            'body' => $result['body'],
            'challenge' => $result['challenge'],
            'counterExample' => $counterExample,
        ]);

        return $this->redirectToRoute('app_home');
    }
}
