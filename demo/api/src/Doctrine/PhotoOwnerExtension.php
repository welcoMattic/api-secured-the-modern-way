<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Photo;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Extension Doctrine qui filtre la collection sur le propriétaire.
 * Sans cette extension, GET /api/photos resterait global même avec l'expression security
 * sur l'item : c'est le point que la démo doit rendre évident.
 */
class PhotoOwnerExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        // Ne s'applique qu'à Photo::class
        if (Photo::class !== $resourceClass) {
            return;
        }

        // Si aucun utilisateur n'est authentifié, ne modifie pas la requête :
        // le firewall a déjà refusé la requête avant d'arriver là, et une extension
        // qui décide de sécurité à sa place serait un piège.
        if (null === $user = $this->security->getUser()) {
            return;
        }

        // getRootAlias() est dépréciée, elle laisserait une dépréciation dans le profiler.
        $alias = $queryBuilder->getRootAliases()[0];
        $sub = $user->getUserIdentifier();

        $queryBuilder
            ->andWhere(sprintf('%s.owner = :sub', $alias))
            ->setParameter('sub', $sub);
    }
}
