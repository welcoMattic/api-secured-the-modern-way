<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Photo;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

/**
 * State processor qui remplit la propriété owner depuis l'utilisateur authentifié au moment du POST.
 * Le client ne peut pas choisir son propriétaire : c'est le serveur qui l'impose, toujours.
 */
#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class PhotoOwnerProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private Security $security,
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): object|array
    {
        // Si on est sur une entité Photo et qu'il y a un utilisateur authentifié, on remplit owner
        if ($data instanceof Photo && null !== $user = $this->security->getUser()) {
            $data->owner = $user->getUserIdentifier();
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
