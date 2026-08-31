<?php

namespace App\Api;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * Un des deux tokens de la session.
     *
     * L'authenticator natif les pose comme attributs du token de sécurité, et
     * AbstractToken::__serialize() inclut les attributs : la paire survit donc dans la
     * session d'une requête à l'autre.
     */
    private function token(string $attribut): string
    {
        $token = $this->tokenStorage->getToken();

        if (!$token?->hasAttribute($attribut)) {
            throw new \LogicException(sprintf(
                'Aucun attribut « %s » sur le token de sécurité : la session n\'a pas été ouverte par l\'authenticator oidc_login.',
                $attribut
            ));
        }

        return $token->getAttribute($attribut);
    }

    /**
     * Liste les photos (GET /api/photos).
     */
    public function list(): array
    {
        return $this->appeler('GET', $this->token('oidc_access_token'), []);
    }

    /**
     * Crée une photo (POST /api/photos).
     */
    public function create(string $title, string $url): array
    {
        return $this->appeler('POST', $this->token('oidc_access_token'), [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'body' => json_encode(['title' => $title, 'url' => $url], \JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * Le contre-exemple : appeler l'API avec l'ID token au lieu de l'access token.
     *
     * L'ID token dit à PhotoBook QUI est l'utilisateur. Son audience est PhotoBook
     * lui-même, pas l'API. Le token handler de l'API valide l'audience : ce jeton
     * parfaitement valide et parfaitement signé se fait donc refuser en 401.
     */
    public function listWithIdToken(): array
    {
        return $this->appeler('GET', $this->token('oidc_id_token'), []);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{status: int, body: string, challenge: ?string}
     */
    private function appeler(string $methode, string $token, array $options): array
    {
        $options['headers'] = ($options['headers'] ?? []) + ['Authorization' => 'Bearer '.$token];

        try {
            $response = $this->httpClient->request($methode, $this->apiBaseUrl.'/api/photos', $options);

            return [
                'status' => $response->getStatusCode(),
                'body' => self::readable($response->getContent(false)),
                // Un 401 n'a pas de corps : l'API dit pourquoi dans cet en-tête.
                'challenge' => $response->getHeaders(false)['www-authenticate'][0] ?? null,
            ];
        } catch (TransportExceptionInterface) {
            // L'API ne répond pas. Sans ce filet, Symfony projetterait une page
            // d'erreur 500 de dev, stack trace comprise, devant la salle.
            return [
                'status' => 0,
                'body' => sprintf("Aucune réponse de %s. L'API est-elle démarrée ?", $this->apiBaseUrl),
                'challenge' => null,
            ];
        }
    }

    /**
     * En dev, l'API joint une trace PHP de plusieurs kilooctets à ses erreurs.
     * Projetée, elle noie la seule ligne qui compte (« Access Denied. »). On la
     * retire à l'affichage, exactement comme le ferait un vrai client.
     */
    private static function readable(string $body): string
    {
        if ('' === $body) {
            return '(corps vide)';
        }

        try {
            $decoded = json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $body;
        }

        if (!\is_array($decoded)) {
            return $body;
        }

        unset($decoded['trace']);

        return json_encode($decoded, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
    }
}
