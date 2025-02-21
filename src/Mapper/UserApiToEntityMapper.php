<?php

namespace App\Mapper;

use App\ApiResource\UserApi;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: UserApi::class, to: User::class)]
readonly class UserApiToEntityMapper implements MapperInterface
{
    public function __construct(
        private UserRepository              $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof UserApi);

        $entity = $dto->id !== null ? $this->userRepository->find($dto->id) : new User();
        if ($entity === null) {
            throw new EntityNotFoundException("User {$dto->id} not found");
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

        return $entity;
    }
}