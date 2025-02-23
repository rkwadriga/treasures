<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata;
use App\Entity\DragonTreasure;
use App\State\EntityClassDtoStateProcessor;
use App\State\EntityToDtoStateProvider;
use App\Validator\IsValidOwner;
use Symfony\Component\Validator\Constraints as Assert;

#[Metadata\ApiResource(
    shortName: 'Treasure',
    operations: [
        new Metadata\Get(),
        new Metadata\GetCollection(),
        new Metadata\Post(
            security: 'is_granted("ROLE_TREASURE_CREATE")',
            validationContext: ['groups' => ['Default', 'PostValidation']],
        ),
        new Metadata\Patch(
            security: 'is_granted("EDIT", object)', // Look at the App\Security\Voter\DragonTreasureVoter
        ),
        new Metadata\Delete(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
    paginationItemsPerPage: 10,
    provider: EntityToDtoStateProvider::class, // This provider converts ORM-entities to DTO-objects (For GET requests)
    processor: EntityClassDtoStateProcessor::class, // This processor converts DTO-objects to ORM-entities (For POST, PUT, PATCH and DELETE requests)
    stateOptions: new Options(entityClass: DragonTreasure::class)
)]
class DragonTreasureApi
{
    #[Metadata\ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id = null;

    #[Assert\NotBlank(groups: ['PostValidation'])]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $name = null;

    #[Assert\NotBlank(groups: ['PostValidation'])]
    public ?string $description = null;

    #[Assert\GreaterThanOrEqual(0)]
    public int $value = 0;

    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(10)]
    public int $coolFactor = 0;

    public ?string $shortDescription = null;

    public ?string $plunderedAtAgo = null;

    public ?bool $isMine = null;

    #[IsValidOwner]
    public ?UserApi $owner = null;
}