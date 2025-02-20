<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\UserApi;
use App\Entity\User;
use ArrayIterator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class EntityToDtoStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: CollectionProvider::class)]
        private ProviderInterface $collectionProvider
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $entities = $this->collectionProvider->provide($operation, $uriVariables, $context);
        assert($entities instanceof Paginator);
        
        $dtos = [];
        foreach ($entities as $entity) {
            $dtos[] = $this->entityToDto($entity);
        }

        return new TraversablePaginator(
            new ArrayIterator($dtos),
            $entities->getCurrentPage(),
            $entities->getItemsPerPage(),
            $entities->getTotalItems(),
        );
    }

    private function entityToDto(object $entity): object
    {
        /** @var User $entity */
        return new UserApi(
            $entity->getId(),
            $entity->getUsername(),
            $entity->getEmail(),
            rand(0, 10),
            $entity->getDragonTreasures()->toArray(),
        );
    }
}
