<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata;
use App\Entity\DragonTreasure;
use App\Entity\User;
use App\State\EntityToDtoStateProvider;

#[Metadata\ApiResource(
    shortName: 'User',
    paginationItemsPerPage: 5,
    provider: EntityToDtoStateProvider::class, // This provider converts ORM-entities to DTO-objects
    stateOptions: new Options(entityClass: User::class)
)]
#[Metadata\ApiFilter(SearchFilter::class, properties: [
    'username' => SearchFilterInterface::STRATEGY_PARTIAL,
])]
class UserApi
{
    public function __construct(
        public ?int $id = null,
        public ?string $email = null,
        public ?string $username = null,
        public ?int $flameThrowingDistance = null,
        /**
         * @var DragonTreasure[]
         */
        public array $dragonTreasures = [],
    ) {
    }
}