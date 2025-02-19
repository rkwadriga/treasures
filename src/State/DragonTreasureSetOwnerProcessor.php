<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
readonly class DragonTreasureSetOwnerProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private Security           $security
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof DragonTreasure && $data->getOwner() === null && $this->security->getUser() !== null) {
            $data->setOwner($this->security->getUser());
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}
