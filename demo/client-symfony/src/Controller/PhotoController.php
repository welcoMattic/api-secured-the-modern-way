<?php

namespace App\Controller;

use App\Api\PhotoApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        ]);

        return $this->redirectToRoute('app_home');
    }

    #[Route('/photos/create', name: 'app_photos_create', methods: ['POST'])]
    public function create(PhotoApiClient $photoApiClient, Request $request): Response
    {
        $result = $photoApiClient->create('Demo Photo', 'https://example.com/demo.jpg');

        $request->getSession()->getFlashBag()->add('api_result', [
            'status' => $result['status'],
            'body' => $result['body'],
        ]);

        return $this->redirectToRoute('app_home');
    }
}
