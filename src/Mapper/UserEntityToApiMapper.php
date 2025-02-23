<?php

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\ApiResource\UserApi;
use App\Entity\DragonTreasure;
use App\Entity\User;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: User::class, to: UserApi::class)]
class UserEntityToApiMapper implements MapperInterface
{
    function __construct(
        private readonly MicroMapperInterface $mapper,
    ) {}

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof User);

        $dto = new UserApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto = $to;
        assert($entity instanceof User);
        assert($dto instanceof UserApi);

        $dto->email = $entity->getEmail();
        $dto->username = $entity->getUsername();
        $dto->flameThrowingDistance = rand(0, 10);
        $dto->dragonTreasures = array_map(fn(DragonTreasure $dragonTreasure) => $this->mapper->map(
            $dragonTreasure, DragonTreasureApi::class, [MicroMapperInterface::MAX_DEPTH => 0]
        ), $entity->getPublishedDragonTreasures()->getValues());

        return $dto;
    }
}