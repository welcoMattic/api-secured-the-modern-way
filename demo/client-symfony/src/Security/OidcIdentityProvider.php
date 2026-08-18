<?php

namespace App\Security;

use Drenso\OidcBundle\Model\OidcTokens;
use Drenso\OidcBundle\Model\OidcUserData;
use Drenso\OidcBundle\Security\UserProvider\OidcUserProviderInterface;
use Symfony\Component\Security\Core\User\OidcUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * Fournisseur d'identité pour le flow authorization_code côté serveur.
 *
 * Volontairement nommé autrement que le OidcUserProvider de l'API : celui de l'API
 * mappe des rôles, celui-ci n'en mappe aucun. Deux classes homonymes aux rôles
 * opposés, projetées l'une après l'autre, sèmeraient la confusion.
 *
 * Symfony sait VÉRIFIER les access tokens (via le token handler "oidc" dans l'API),
 * mais ne sait pas encore INITIER le flow authorization_code (cf PR symfony/symfony#64954).
 * On utilise donc drenso/symfony-oidc-bundle pour gérer le flow côté client.
 */
class OidcIdentityProvider implements OidcUserProviderInterface
{
    private ?OidcUserData $userData = null;

    /**
     * Appelé EN PREMIER par OidcAuthenticator::authenticate().
     *
     * On stocke les données utilisateur pour les réutiliser dans loadOidcUser().
     * Aucune base locale : l'identité vient uniquement du Provider.
     */
    public function ensureUserExists(string $userIdentifier, OidcUserData $userData, OidcTokens $tokens): void
    {
        $this->userData = $userData;
    }

    /**
     * Appelé EN DEUXIÈME par OidcAuthenticator::authenticate().
     *
     * L'utilisateur créé ici n'a QUE le rôle ROLE_USER.
     * Les permissions photos (ROLE_PHOTOS_WRITE, etc.) sont gérées par l'API
     * à partir de l'access token, pas ici. On ne lit donc PAS realm_access.
     */
    public function loadOidcUser(string $userIdentifier): UserInterface
    {
        return new OidcUser(
            userIdentifier: $userIdentifier,
            roles: ['ROLE_USER'],
            sub: $this->userData?->getSub(),
            email: $this->userData?->getEmail(),
            preferredUsername: $this->userData?->getDisplayName(),
        );
    }

    /**
     * Appelé à chaque requête suivante par le listener de session.
     * L'identité vit dans la session, rien à recharger.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return OidcUser::class === $class;
    }

    /**
     * Exigée par UserProviderInterface, mais jamais appelée : l'authentificateur OIDC
     * passe par loadOidcUser(), et refreshUser() rend l'utilisateur tel quel. Y déléguer
     * produirait un utilisateur sans identité, puisque $userData ne serait pas renseigné.
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new UnsupportedUserException(sprintf('%s ne charge un utilisateur que via loadOidcUser().', self::class));
    }
}
