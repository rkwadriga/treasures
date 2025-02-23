<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\DragonTreasureApi;
use App\Entity\DragonTreasure;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

readonly class DragonTreasureStateProcessor implements ProcessorInterface
{
    function __construct(
        private EntityClassDtoStateProcessor $innerProcessor,
        private EntityManagerInterface $entityManager,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        $previousData = $context['previous_data'] ?? null;
        if ($data instanceof DragonTreasureApi
            && $previousData instanceof DragonTreasureApi
            && !$previousData->isPublished
            && $data->isPublished
        ) {
            $dragonTreasure = $this->entityManager->getRepository(DragonTreasure::class)->find($data->id);
            if (!$dragonTreasure instanceof DragonTreasure) {
                throw new LogicException(sprintf(
                    'Entity %s #%s not found',
                    DragonTreasure::class,
                    $data->id
                ));
            }

            $notification = new Notification();
            $notification->setDragonTreasure($dragonTreasure);
            $notification->setMessage('Treasure has been published!');
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }

        return $result;
    }
}
