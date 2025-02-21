<?php

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\ApiResource\UserApi;
use App\Entity\DragonTreasure;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: DragonTreasure::class, to: DragonTreasureApi::class)]
readonly class DragonTreasureEntityToApiMapper implements MapperInterface
{
    function __construct(
        private MicroMapperInterface $mapper,
    ) {}

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;
        assert($entity instanceof DragonTreasure);

        $dto = new DragonTreasureApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto = $to;
        assert($entity instanceof DragonTreasure);
        assert($dto instanceof DragonTreasureApi);

        $dto->name = $entity->getName();
        $dto->description = $entity->getDescription();
        $dto->value = $entity->getValue();
        $dto->coolFactor = $entity->getCoolFactor();
        $dto->shortDescription = $entity->getShortDescription();
        $dto->plunderedAtAgo = $entity->getPlunderedAtAgo();
        $dto->isMine = true;
        $dto->owner = $entity->getOwner() !== null
            ? $this->mapper->map($entity->getOwner(), UserApi::class)
            : null;

        return $dto;
    }
}