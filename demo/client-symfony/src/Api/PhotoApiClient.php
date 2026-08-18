<?php

namespace App\Api;

use Drenso\OidcBundle\Security\Token\OidcToken;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Client HTTP pour appeler l'API au nom de l'utilisateur connecté.
 */
class PhotoApiClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private TokenStorageInterface $tokenStorage,
        #[Autowire('%env(API_BASE_URL)%')]
        private string $apiBaseUrl,
    ) {
    }

    /**
     * Récupère l'access token depuis le token de sécurité.
     *
     * Le token OidcToken stocke l'objet OidcTokens comme attribut,
     * et AbstractToken::__serialize() inclut les attributs.
     * L'access token survit donc dans la session entre les requêtes.
     */
    private function accessToken(): string
    {
        $token = $this->tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            throw new \LogicException('Aucun token de sécurité disponible.');
        }

        if (!$token instanceof OidcToken) {
            throw new \LogicException(sprintf(
                'Token de type %s non supporté : attendu Drenso\\OidcBundle\\Security\\Token\\OidcToken.',
                $token::class
            ));
        }

        return $token->getAuthData()->getAccessToken();
    }

    /**
     * Liste les photos (GET /api/photos).
     */
    public function list(): array
    {
        $response = $this->httpClient->request('GET', $this->apiBaseUrl.'/api/photos', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ],
        ]);

        return [
            'status' => $response->getStatusCode(),
            'body' => $response->getContent(false),
        ];
    }

    /**
     * Crée une photo (POST /api/photos).
     */
    public function create(string $title, string $url): array
    {
        $response = $this->httpClient->request('POST', $this->apiBaseUrl.'/api/photos', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->accessToken(),
                'Content-Type' => 'application/ld+json',
            ],
            'body' => json_encode([
                'title' => $title,
                'url' => $url,
            ]),
        ]);

        return [
            'status' => $response->getStatusCode(),
            'body' => $response->getContent(false),
        ];
    }
}
