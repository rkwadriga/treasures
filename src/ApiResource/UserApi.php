<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\DragonTreasure;
use App\Entity\User;
use App\State\EntityClassDtoStateProcessor;
use App\State\EntityToDtoStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[Metadata\ApiResource(
    shortName: 'User',
    operations: [
        new Metadata\Get(),
        new GetCollection(),
        new Metadata\Post(
            security: 'is_granted("PUBLIC_ACCESS")',
            validationContext: ['groups' => ['Default', 'PostValidation']],
        ),
        new Metadata\Patch(
            security: 'is_granted("ROLE_USER_EDIT")',
        ),
        new Metadata\Delete(),
    ],
    //normalizationContext: [AbstractNormalizer::IGNORED_ATTRIBUTES => ['flameThrowingDistance']], // Do not show selected fields in responses
    //denormalizationContext: [AbstractNormalizer::IGNORED_ATTRIBUTES => ['flameThrowingDistance']], // Do not write selected attributes values from request input
    paginationItemsPerPage: 5,
    security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class, // This provider converts ORM-entities to DTO-objects (For GET requests)
    processor: EntityClassDtoStateProcessor::class, // This processor converts DTO-objects to ORM-entities (For POST, PUT, PATCH and DELETE requests)
    stateOptions: new Options(entityClass: User::class)
)]
#[Metadata\ApiFilter(SearchFilter::class, properties: [
    'username' => SearchFilterInterface::STRATEGY_PARTIAL,
])]
class UserApi
{
    #[Metadata\ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $username = null;

    #[Metadata\ApiProperty(readable: false)]
    #[Assert\NotBlank(groups: ['PostValidation'])]
    public ?string $password = null;

    #[Metadata\ApiProperty(writable: false)]
    public ?int $flameThrowingDistance = null;

    /**
     * @var DragonTreasure[]
     */
    #[Metadata\ApiProperty(writable: false)]
    public array $dragonTreasures = [];
}