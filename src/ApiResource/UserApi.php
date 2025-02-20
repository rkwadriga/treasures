<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata;
use App\Entity\DragonTreasure;
use App\Entity\User;
use App\State\EntityClassDtoStateProcessor;
use App\State\EntityToDtoStateProvider;

#[Metadata\ApiResource(
    shortName: 'User',
    paginationItemsPerPage: 5,
    provider: EntityToDtoStateProvider::class, // This provider converts ORM-entities to DTO-objects (For GET requests)
    processor: EntityClassDtoStateProcessor::class, // This processor converts DTO-objects to ORM-entities (For POST, PUT, PATCH and DELETE requests)
    stateOptions: new Options(entityClass: User::class)
)]
#[Metadata\ApiFilter(SearchFilter::class, properties: [
    'username' => SearchFilterInterface::STRATEGY_PARTIAL,
])]
class UserApi
{
    public function __construct(
        #[Metadata\ApiProperty(readable: false, identifier: true)]
        public ?int $id = null,
        public ?string $email = null,
        public ?string $username = null,
        #[Metadata\ApiProperty(readable: false)]
        public ?string $password = null,
        public ?int $flameThrowingDistance = null,
        /**
         * @var DragonTreasure[]
         */
        public array $dragonTreasures = [],
    ) {
    }
}