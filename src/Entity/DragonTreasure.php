<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter as Filters;
use ApiPlatform\Metadata;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\DragonTreasureRepository;
use Carbon\Carbon;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: DragonTreasureRepository::class)]
#[Metadata\ApiResource(
    shortName: 'Treasure',
    description: 'A rare and valuable treasure',
    operations: [
        new Metadata\GetCollection(),
        new Metadata\Get(
            normalizationContext: [
                'groups' => ['treasure:read', 'treasure:item:get'],
            ],
        ),
        new Metadata\Post(security: 'is_granted("ROLE_TREASURE_CREATE")'),
        new Metadata\Put(
            security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_TREASURE_EDIT") and object.getOwner() == user)',
            securityPostDenormalize: 'is_granted("ROLE_ADMIN") or object.getOwner() == user'
        ),
        new Metadata\Patch(
            security: 'is_granted("EDIT", object)', // See the App\Entity\ApiToken\DragonTreasureVoter
            securityPostDenormalize: 'is_granted("EDIT", object)' // See the App\Entity\ApiToken\DragonTreasureVoter
        ),
        new Metadata\Delete(security: 'is_granted("ROLE_ADMIN")'),
    ],
    formats: ['jsonld', 'json', 'csv' => 'text/csv'], // jsonld and json described in config\packages\api_platform.yaml, csv added
    normalizationContext: [
        'groups' => ['treasure:read'],
    ],
    denormalizationContext: [
        'groups' => ['treasure:write'],
    ],
    paginationItemsPerPage: 10,
)]
#[Metadata\ApiResource( // Allows to select all treasures of specific user
    uriTemplate: '/users/{user_id}/treasures.{_format}',
    shortName: 'Treasure',
    operations: [new Metadata\GetCollection()],
    uriVariables: [
        'user_id' => new Metadata\Link(fromProperty: 'dragonTreasures', fromClass: User::class, description: 'User identifier'),
    ],
    normalizationContext: [
        'groups' => ['treasure:read'],
    ],
)]
#[Metadata\ApiFilter(PropertyFilter::class)] // Allows to get only selected fields
#[Metadata\ApiFilter(Filters\SearchFilter::class, properties: ['owner.username' => SearchFilterInterface::STRATEGY_PARTIAL])] // Allows to filter entities by owners (Like GET /treasures?owner.username=<part_of_username>)
class DragonTreasure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 255)]
    #[Groups(['treasure:read', 'treasure:write', 'user:read', 'user:write'])] // Group "user:item:get" needed to show this attribute only in "GET /users/<id>" request, "user:write" - for writing this attribute in "PATCH /users/<id>" request
    #[Metadata\ApiFilter(Filters\SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_PARTIAL)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255, maxMessage: 'Describe your loot in 255 characters or less.')]
    private ?string $name;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups('treasure:read')]
    #[Metadata\ApiFilter(Filters\SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_PARTIAL)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column]
    #[Metadata\ApiProperty(description: 'The estimated value of this treasure, in gold coins')]
    #[Groups(['treasure:read', 'treasure:write', 'user:read', 'user:write'])]// Group "user:item:get" needed to show this attribute only in "GET /user/<id>" request, "user:write" - for writing this attribute in "PATCH /users/<id>" request
    #[Metadata\ApiFilter(Filters\RangeFilter::class)]
    #[Assert\GreaterThanOrEqual(0)]
    private int $value = 0;

    #[ORM\Column]
    #[Groups(['treasure:read', 'treasure:write'])]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(10)]
    private int $coolFactor = 0;

    #[ORM\Column]
    private DateTimeImmutable $plunderedAt;

    #[ORM\Column]
    #[Metadata\ApiFilter(Filters\BooleanFilter::class)]
    // #[Groups(['treasure:read', 'treasure:write'])]
    //#[Metadata\ApiProperty(security: 'is_granted("EDIT", object)')] // See the App\Security\Voter\DragonTreasureVoter
    #[Groups(['admin:read', 'admin:write'])] // See the App\ApiPlatform\AminGroupsContextBuilder (it adds the "admin:read" group on serializing and "admin:write" group on deserializing)
    private bool $isPublished;

    #[ORM\ManyToOne(inversedBy: 'dragonTreasures')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['treasure:read', 'treasure:write'])]
    #[Assert\NotBlank]
    #[Assert\Valid] // It's needed for use User validation on updating user in request "PATCH /treasures/<id>" request
    #[Metadata\ApiFilter(Filters\SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)] // Allows to filter entities by owners (Like GET /treasures?owner=/api/users/<user_id>)
    private ?User $owner = null;

    public function __construct(?string $name = null)
    {
        $this->plunderedAt = new DateTimeImmutable();
        $this->isPublished = false;
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    #[Groups('treasure:read')]
    public function getShortDescription(): ?string
    {
        return u($this->description)->truncate(40, '...');
    }

    #[Metadata\ApiProperty(description: 'Set multi-lined treasure description')]
    #[Groups(['treasure:write', 'user:write'])] // Group "user:write" needed for writing this attribute in "PATCH /users/<id>" request
    #[SerializedName('description')]
    public function setTextDescription(string $description): static
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getCoolFactor(): int
    {
        return $this->coolFactor;
    }

    public function setCoolFactor(int $coolFactor): static
    {
        $this->coolFactor = $coolFactor;

        return $this;
    }

    public function getPlunderedAt(): DateTimeImmutable
    {
        return $this->plunderedAt;
    }

    public function setPlunderedAt(DateTimeImmutable $plunderedAt): static
    {
        $this->plunderedAt = $plunderedAt;

        return $this;
    }

    #[Metadata\ApiProperty(description: 'A human-readable representation of when this treasure was plundered')]
    #[Groups('treasure:read')]
    public function getPlunderedAtAgo(): string
    {
        return Carbon::instance($this->getPlunderedAt())->diffForHumans();
    }

    public function getIsPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
