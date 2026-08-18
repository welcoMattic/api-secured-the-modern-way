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
        $result = $photoApiClient->list();

        $request->getSession()->getFlashBag()->add('api_result', [
            'status' => $result['status'],
            'body' => $result['body'],
            'challenge' => $result['challenge'],
        ]);

        return $this->redirectToRoute('app_home');
    }

    #[Route('/photos/create', name: 'app_photos_create', methods: ['POST'])]
    public function create(PhotoApiClient $photoApiClient, Request $request): Response
    {
        $result = $photoApiClient->create('Photo créée depuis le client Symfony', 'https://example.com/symfony.jpg');

        $request->getSession()->getFlashBag()->add('api_result', [
            'status' => $result['status'],
            'body' => $result['body'],
            'challenge' => $result['challenge'],
        ]);

        return $this->redirectToRoute('app_home');
    }
}
