<?php

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\DragonTreasure;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

readonly class DragonTreasureIsPublishedExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addIsPublishedEqualsTrueConditionToQuery($resourceClass, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addIsPublishedEqualsTrueConditionToQuery($resourceClass, $queryBuilder);
    }

    public function addIsPublishedEqualsTrueConditionToQuery(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        if ($resourceClass !== DragonTreasure::class || $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($user !== null) {
            $queryBuilder
                ->andWhere(sprintf('%s.isPublished = :is_published OR %s.owner = :current_user', $rootAlias, $rootAlias))
                ->setParameter('current_user', $user);
        } else {
            $queryBuilder
                ->andWhere(sprintf('%s.isPublished = :is_published', $rootAlias));
        }

        $queryBuilder->setParameter('is_published', true);
    }
}