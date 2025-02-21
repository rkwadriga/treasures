<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata;
use App\Entity\DragonTreasure;
use App\State\EntityClassDtoStateProcessor;
use App\State\EntityToDtoStateProvider;

#[Metadata\ApiResource(
    shortName: 'Treasure',
    paginationItemsPerPage: 10,
    provider: EntityToDtoStateProvider::class, // This provider converts ORM-entities to DTO-objects (For GET requests)
    processor: EntityClassDtoStateProcessor::class, // This processor converts DTO-objects to ORM-entities (For POST, PUT, PATCH and DELETE requests)
    stateOptions: new Options(entityClass: DragonTreasure::class)
)]
class DragonTreasureApi
{
    #[Metadata\ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $description = null;

    public int $value = 0;

    public int $coolFactor = 0;

    public ?string $shortDescription = null;

    public ?string $plunderedAtAgo = null;

    public ?bool $isMine = null;

    public ?UserApi $owner = null;
}