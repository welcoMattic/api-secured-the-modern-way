<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\OidcUser;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Provider OIDC : mappe les claims du token vers un objet utilisateur Symfony.
 * OIDC n'a aucune notion de role : ce mapping est à notre charge.
 */
final class OidcUserProvider implements AttributesBasedUserProviderInterface
{
    /**
     * Charge l'utilisateur à partir de l'identifiant et des claims du token.
     * $attributes contient TOUS les claims du token JWT.
     */
    public function loadUserByIdentifier(string $identifier, array $attributes = []): OidcUser
    {
        return new OidcUser(
            userIdentifier: $identifier,
            roles: $this->mapRoles($attributes),
            sub: $attributes['sub'] ?? null,
            email: $attributes['email'] ?? null,
            preferredUsername: $attributes['preferred_username'] ?? null,
        );
    }

    /**
     * Rafraîchit l'utilisateur : jamais appelé (firewall stateless).
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new UnsupportedUserException('Le firewall est stateless, le refresh n\'est pas supporté.');
    }

    /**
     * Vérifie que ce provider supporte la classe donnée.
     */
    public function supportsClass(string $class): bool
    {
        return OidcUser::class === $class;
    }

    /**
     * Mappe les rôles Keycloak (claims realm_access.roles) vers des rôles Symfony.
     * - TOUJOURS ROLE_USER
     * - Chaque rôle dans realm_access.roles devient ROLE_ + le rôle en majuscules
     */
    private function mapRoles(array $attributes): array
    {
        $roles = ['ROLE_USER'];

        // realm_access.roles est un tableau de rôles Keycloak
        if (isset($attributes['realm_access']['roles']) && \is_array($attributes['realm_access']['roles'])) {
            foreach ($attributes['realm_access']['roles'] as $role) {
                $roles[] = 'ROLE_' . \strtoupper($role);
            }
        }

        return $roles;
    }
}
