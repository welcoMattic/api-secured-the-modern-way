<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

/**
 * Décoration du provider Doctrine de collection : un jeton consommé par requête,
 * clé = identifiant de l'utilisateur (le `sub` du token, jamais l'IP : derrière une
 * gateway l'IP est celle du proxy), 429 automatique via l'exception.
 */
final class RateLimitedProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $inner,
        private RateLimiterFactoryInterface $apiLimiter,
        private Security $security,
    ) {}

    public function provide(Operation $op, array $uriVariables = [], array $context = []): object|array|null {
        $key = $this->security->getUser()?->getUserIdentifier(); // le sub du token
        $limit = $this->apiLimiter->create($key)->consume();
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp() - time());
        }

        return $this->inner->provide($op, $uriVariables, $context);
    }
}
