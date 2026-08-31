<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\OidcUser;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Provider OIDC : mappe les claims du token vers un objet utilisateur Symfony.
 * OIDC n'a aucune notion de rôle : ce mapping est à notre charge.
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
            preferredUsername: $attributes['preferred_username'] ?? null,
            email: $attributes['email'] ?? null,
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new UnsupportedUserException('Le firewall est stateless, le refresh n\'est pas supporté.');
    }

    public function supportsClass(string $class): bool
    {
        return OidcUser::class === $class;
    }

    /**
     * Mappe les claims du token vers des rôles Symfony.
     *
     * realm_access.roles ne contient pas les rôles d'Alice : il contient ceux
     * qu'Alice a accordés à CETTE application. Le Provider a déjà croisé les deux
     * (Keycloak : « full scope allowed » désactivé, plus un role scope mapping sur
     * les client scopes photos:read et photos:write). Un access token ne porte que
     * l'autorité réellement déléguée, et le resource server n'a plus qu'à la lire.
     *
     * Rien à croiser ici, donc, et le mapping fonctionne à l'identique avec le
     * token handler offline et le token handler online.
     */
    private function mapRoles(array $attributes): array
    {
        $roles = ['ROLE_USER'];

        foreach ($attributes['realm_access']['roles'] ?? [] as $realmRole) {
            if (\in_array($realmRole, ['PHOTOS_READ', 'PHOTOS_WRITE'], true)) {
                $roles[] = 'ROLE_'.$realmRole;
            }
        }

        return $roles;
    }
}
