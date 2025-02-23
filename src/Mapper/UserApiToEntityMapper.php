<?php

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\ApiResource\UserApi;
use App\Entity\DragonTreasure;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: UserApi::class, to: User::class)]
readonly class UserApiToEntityMapper implements MapperInterface
{
    public function __construct(
        private MicroMapperInterface        $mapper,
        private UserRepository              $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private PropertyAccessorInterface   $propertyAccessor,
    ) {}

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof UserApi);

        $entity = $dto->id !== null ? $this->userRepository->find($dto->id) : new User();
        if ($entity === null) {
            throw new EntityNotFoundException(sprintf('Entity %s #%s not found', User::class, $dto->id));
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto = $from;
        $entity = $to;
        assert($dto instanceof UserApi);
        assert($entity instanceof User);

        $entity
            ->setEmail($dto->email)
            ->setUsername($dto->username)
        ;
        if ($dto->password !== null) {
            $entity->setPassword($this->passwordHasher->hashPassword($entity, $dto->password));
        }
        $this->propertyAccessor->setValue($entity, 'dragonTreasures', array_map(
            fn(DragonTreasureApi $treasureDto) => $this->mapper->map($treasureDto, DragonTreasure::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]),
            $dto->dragonTreasures ?: [],
        ));

        return $entity;
    }
}