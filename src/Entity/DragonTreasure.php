<?php

namespace App\Entity;

use ApiPlatform\Metadata;
use App\Repository\DragonTreasureRepository;
use Carbon\Carbon;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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
)]
class DragonTreasure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    #[Metadata\ApiProperty(description: 'The estimated value of this treasure, in gold coins')]
    private ?int $value = null;

    #[ORM\Column]
    private ?int $coolFactor = null;

    #[ORM\Column]
    private DateTimeImmutable $plunderedAt;

    #[ORM\Column]
    private bool $isPublished;

    public function __construct()
    {
        $this->plunderedAt = new DateTimeImmutable();
        $this->isPublished = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setTextDescription(string $description): static
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getCoolFactor(): ?int
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

    /**
     * A human-readable representation of when this treasure was plundered
     */
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
