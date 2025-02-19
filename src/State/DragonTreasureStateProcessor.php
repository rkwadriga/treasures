<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\DragonTreasure;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

// #[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
readonly class DragonTreasureStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $innerProcessor,
        private Security           $security,
        private EntityManagerInterface $entityManager,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        assert($data instanceof DragonTreasure);

        if ($data->getOwner() === null && $this->security->getUser() !== null) {
            $data->setOwner($this->security->getUser());
        }

        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        $data->setIsOwnedByAuthenticatedUser(
            $data->getOwner() !== null && $this->security->getUser() === $data->getOwner()
        );

        $previousData = $context['previous_data'] ?? null;
        if ($previousData instanceof DragonTreasure &&
            !$previousData->getIsPublished() &&
            $previousData->getIsPublished() !== $data->getIsPublished()
        ) {
            $notification = new Notification();
            $notification->setDragonTreasure($data);
            $notification->setMessage('Treasure has been published!');
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }

        return $result;
    }
}
