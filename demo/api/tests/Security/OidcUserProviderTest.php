<?php

namespace App\Tests\Security;

use App\Security\OidcUserProvider;
use App\Tests\Oidc\TokenFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\OidcUser;

/**
 * Le seul endroit de l'API qui traduit des claims en rôles Symfony.
 *
 * Il ne croise rien : CloudPics ID a déjà réduit realm_access.roles à l'intersection
 * des rôles du compte et des scopes accordés au client. Ces tests fixent ce contrat,
 * qui est aussi ce qui permet au mapping de fonctionner à l'identique en offline et
 * en online, là où le endpoint userinfo ne renvoie aucun claim « scope ».
 */
final class OidcUserProviderTest extends TestCase
{
    /**
     * @param list<string> $realmRoles
     * @param list<string> $attendus
     */
    #[DataProvider('mappings')]
    public function testLesRolesRealmDeviennentDesRolesSymfony(array $realmRoles, array $attendus): void
    {
        $user = (new OidcUserProvider())->loadUserByIdentifier(TokenFactory::ALICE, [
            'sub' => TokenFactory::ALICE,
            'realm_access' => ['roles' => $realmRoles],
        ]);

        self::assertSame($attendus, $user->getRoles());
    }

    public static function mappings(): iterable
    {
        yield 'aucun rôle : authentifié, et rien de plus' => [
            [],
            ['ROLE_USER'],
        ];
        yield 'lecture seule, le compte de bob' => [
            ['PHOTOS_READ'],
            ['ROLE_USER', 'ROLE_PHOTOS_READ'],
        ];
        yield 'lecture et écriture, le compte d\'alice' => [
            ['PHOTOS_READ', 'PHOTOS_WRITE'],
            ['ROLE_USER', 'ROLE_PHOTOS_READ', 'ROLE_PHOTOS_WRITE'],
        ];
        yield 'un rôle realm inconnu de l\'API est ignoré' => [
            ['PHOTOS_READ', 'default-roles-photos', 'offline_access'],
            ['ROLE_USER', 'ROLE_PHOTOS_READ'],
        ];
    }

    public function testSansClaimRealmAccessLUtilisateurResteAuthentifie(): void
    {
        $user = (new OidcUserProvider())->loadUserByIdentifier(TokenFactory::BOB, ['sub' => TokenFactory::BOB]);

        self::assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testLesClaimsDIdentiteSontPortesParLUtilisateur(): void
    {
        $user = (new OidcUserProvider())->loadUserByIdentifier(TokenFactory::ALICE, [
            'sub' => TokenFactory::ALICE,
            'preferred_username' => 'alice',
            'email' => 'alice@example.com',
        ]);

        self::assertInstanceOf(OidcUser::class, $user);
        self::assertSame(TokenFactory::ALICE, $user->getUserIdentifier());
        self::assertSame('alice', $user->getPreferredUsername());
        self::assertSame('alice@example.com', $user->getEmail());
    }

    /**
     * Le firewall est stateless : rafraîchir un utilisateur n'a aucun sens, et le
     * laisser passer silencieusement masquerait une session mal configurée.
     */
    public function testLeRafraichissementNEstPasSupporte(): void
    {
        $provider = new OidcUserProvider();
        $user = $provider->loadUserByIdentifier(TokenFactory::ALICE, ['sub' => TokenFactory::ALICE]);

        $this->expectException(UnsupportedUserException::class);
        $provider->refreshUser($user);
    }
}
