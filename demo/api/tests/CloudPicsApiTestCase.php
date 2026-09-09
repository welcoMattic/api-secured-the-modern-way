<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Photo;
use App\Tests\Oidc\TokenFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Base des tests fonctionnels : une base neuve et les mêmes photos qu'en démo.
 *
 * Les jeux de données de « castor db:reset » et des tests sont volontairement
 * identiques (deux photos pour alice, une pour bob) : ce qui se voit à l'écran est
 * ce qui est testé.
 */
abstract class CloudPicsApiTestCase extends ApiTestCase
{
    /**
     * createClient() démarre le noyau lui-même. Le dire explicitement, plutôt que de
     * laisser la valeur par défaut, évite la dépréciation d'API Platform 4.1 et fixe
     * le comportement avant la bascule de la 5.0.
     */
    protected static ?bool $alwaysBootKernel = true;

    /** Les identifiants des photos semées, dans l'ordre d'insertion. */
    protected array $photosAlice = [];
    protected int $photoBob;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $schema = new SchemaTool($em);
        $classes = $em->getMetadataFactory()->getAllMetadata();
        $schema->dropSchema($classes);
        $schema->createSchema($classes);

        $this->photosAlice = [
            $this->seed('Coucher de soleil sur le Golden Gate', TokenFactory::ALICE, $em),
            $this->seed('Alice au sommet du Mont Tamalpais', TokenFactory::ALICE, $em),
        ];
        $this->photoBob = $this->seed('Bob en lecture seule', TokenFactory::BOB, $em);

        // Le quota de test est de trois requêtes et le pool est un fichier qui survit
        // d'un test à l'autre, chaque test repart donc d'un seau plein.
        self::getContainer()->get('cache.rate_limiter.test')->clear();

        // Le client rebootera le noyau : la base doit être un fichier, pas :memory:.
        self::ensureKernelShutdown();
    }

    private function seed(string $title, string $owner, EntityManagerInterface $em): int
    {
        $photo = new Photo();
        $photo->title = $title;
        $photo->url = 'https://cloudpics.example/'.md5($title).'.jpg';
        $photo->owner = $owner;

        $em->persist($photo);
        $em->flush();

        return $photo->getId();
    }
}
