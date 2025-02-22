<?php

namespace App\Entity;

use ApiPlatform\Metadata;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\UserRepository;
use App\Validator\TreasuresAllowedOwnerChange;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[Metadata\ApiResource(
    operations: [
        new Metadata\GetCollection(),
        new Metadata\Get(
            normalizationContext: [
                'groups' => ['user:read', 'user:item:get'],
            ],
        ),
        new Metadata\Post(
            security: 'is_granted("PUBLIC_ACCESS")',
            validationContext: ['groups' => ['Default', 'postValidation']] // This needed to validate the plainPassword property only on "POST /api/users" request
        ),
        new Metadata\Put(security: 'is_granted("ROLE_USER_EDIT")'),
        new Metadata\Patch(security: 'is_granted("ROLE_USER_EDIT")'),
        new Metadata\Delete(security: 'is_granted("ROLE_ADMIN")'),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    security: 'is_granted("ROLE_USER")',
)]
#[Metadata\ApiResource( // Allows to select owner of specific treasure
    uriTemplate: '/treasures/{treasure_id}/owner.{_format}',
    operations: [new Metadata\Get()],
    uriVariables: [
        'treasure_id' => new Metadata\Link(fromProperty: 'owner', fromClass: DragonTreasure::class, description: 'Treasure identifier'),
    ],
    normalizationContext: [
        'groups' => ['user:read'],
    ],
    security: 'is_granted("ROLE_USER")',
)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[Metadata\ApiFilter(PropertyFilter::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['user:read', 'user:write', 'treasure:item:get', 'treasure:write'])] // Group "treasure:item:get" needed to show this attribute only in "GET /treasures/<id>" request, "treasure:write" - for writing this attribute in "PATCH /treasures/<id>" request
    #[Assert\NotBlank]
    private ?string $username = null;

    /**
     * @var Collection<int, DragonTreasure>
     */
    #[ORM\OneToMany(targetEntity: DragonTreasure::class, mappedBy: 'owner', cascade: ['persist'], orphanRemoval: true)] // "cascade: ['persist']" means that you can create a new DragonTreasure on creating/updating the user, "orphanRemoval: true" means that you can delete a new DragonTreasure on updating the user
    #[Groups(['user:write'])]
    #[Assert\Valid] // It's needed for use User validation on updating user in request "PATCH /users/<id>" request
    #[TreasuresAllowedOwnerChange] // Look for App\Validator\TreasuresAllowedOwnerChangeValidator
    private Collection $dragonTreasures;

    /**
     * @var Collection<int, ApiToken>
     */
    #[ORM\OneToMany(targetEntity: ApiToken::class, mappedBy: 'ownedBy')]
    private Collection $apiTokens;

    /** Scopes given during API authentication */
    private ?array $accessTokenScopes = null;


    #[Groups('user:write')]
    #[SerializedName('password')]
    #[Assert\NotBlank(groups: ['postValidation'])] // This needed to run validation only on "POST /api/users" request
    #[Assert\Length(min: 4, max: 255, maxMessage: 'The password should be between 4 and 255 characters.')]
    private ?string $plainPassword = null; // Look for App\State\UserHashPasswordProcessor

    public function __construct()
    {
        $this->dragonTreasures = new ArrayCollection();
        $this->apiTokens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        if ($this->accessTokenScopes === null) {
            // Logged in as a full, normal user
            $roles = $this->roles;
            $roles[] = 'ROLE_FULL_USER';
        } else {
            // Authenticated by token
            $roles = $this->accessTokenScopes;
        }

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, DragonTreasure>
     */
    public function getDragonTreasures(): Collection
    {
        return $this->dragonTreasures;
    }

    /**
     * @return Collection<int, DragonTreasure>
     */
    #[Groups(['user:read'])]
    #[SerializedName('dragonTreasures')]
    public function getPublishedDragonTreasures(): Collection
    {
        return $this->dragonTreasures->filter(fn (DragonTreasure $dragonTreasure) => $dragonTreasure->getIsPublished());
    }

    public function addDragonTreasure(DragonTreasure $dragonTreasure): static
    {
        if (!$this->dragonTreasures->contains($dragonTreasure)) {
            $this->dragonTreasures->add($dragonTreasure);
            $dragonTreasure->setOwner($this);
        }

        return $this;
    }

    public function removeDragonTreasure(DragonTreasure $dragonTreasure): static
    {
        if ($this->dragonTreasures->removeElement($dragonTreasure)) {
            // set the owning side to null (unless already changed)
            if ($dragonTreasure->getOwner() === $this) {
                $dragonTreasure->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ApiToken>
     */
    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): static
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens->add($apiToken);
            $apiToken->setOwnedBy($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): static
    {
        if ($this->apiTokens->removeElement($apiToken)) {
            // set the owning side to null (unless already changed)
            if ($apiToken->getOwnedBy() === $this) {
                $apiToken->setOwnedBy(null);
            }
        }

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getValidTokenStrings(): array
    {
        return $this->getApiTokens()
            ->filter(fn (ApiToken $token) => $token->isValid())
            ->map(fn (ApiToken $token) => $token->getToken())
            ->toArray();
    }

    public function markAsTokenAuthenticated(array $scopes): void
    {
        $this->accessTokenScopes = $scopes;
    }
}
