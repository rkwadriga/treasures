<?php

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\Entity\DragonTreasure;
use App\Entity\User;
use App\Repository\DragonTreasureRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: DragonTreasureApi::class, to: DragonTreasure::class)]
readonly class DragonTreasureApiToEntityMapper implements MapperInterface
{
    function __construct(
        private MicroMapperInterface     $mapper,
        private DragonTreasureRepository $dragonTreasureRepository,
        private Security                 $security,
    ) {}

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        assert($dto instanceof DragonTreasureApi);

        $entity = $dto->id !== null ? $this->dragonTreasureRepository->find($dto->id) : new DragonTreasure($dto->name);
        if ($entity === null) {
            throw new EntityNotFoundException(sprintf(
                'Entity %s #%s not found', DragonTreasure::class, $dto->id
            ));
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto = $from;
        $entity = $to;
        assert($dto instanceof DragonTreasureApi);
        assert($entity instanceof DragonTreasure);

        $entity->setTextDescription($dto->description);
        $entity->setValue($dto->value);
        $entity->setCoolFactor($dto->coolFactor);
        $entity->setIsPublished($dto->isPublished);
        if ($dto->owner !== null) {
            $entity->setOwner($this->mapper->map($dto->owner, User::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]));
        } elseif ($this->security->getUser() !== null) {
            $entity->setOwner($this->security->getUser());
        }

        return $entity;
    }
}