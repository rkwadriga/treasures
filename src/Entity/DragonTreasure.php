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
        new Metadata\Get(),
        new Metadata\Post(),
        new Metadata\Put(),
        new Metadata\Patch(),
        new Metadata\Delete(),
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
#[Metadata\ApiFilter(PropertyFilter::class)] // Allows to get only selected fields
class DragonTreasure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 255)]
    #[Groups(['treasure:read', 'treasure:write'])]
    #[Metadata\ApiFilter(Filters\SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_PARTIAL)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50, maxMessage: 'Describe your loot in 50 characters or less.')]
    private ?string $name;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups('treasure:read')]
    #[Metadata\ApiFilter(Filters\SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_PARTIAL)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column]
    #[Metadata\ApiProperty(description: 'The estimated value of this treasure, in gold coins')]
    #[Groups(['treasure:read', 'treasure:write'])]
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
    private bool $isPublished;

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
    #[Groups('treasure:write')]
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
}
