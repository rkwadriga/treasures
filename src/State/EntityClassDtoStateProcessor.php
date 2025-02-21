<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UserApi;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfonycasts\MicroMapper\MicroMapperInterface;

readonly class EntityClassDtoStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $persistProcessor,
        #[Autowire(service: RemoveProcessor::class)] private ProcessorInterface  $removeProcessor,
        private MicroMapperInterface                                             $microMapper,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        assert($data instanceof UserApi);
        if (isset($uriVariables['id']) && $uriVariables['id'] !== $data->id) {
            throw new UnprocessableEntityHttpException('You cannot change the id of this entity.');
        }

        $entity = $this->mapDtoToEntity($data);

        if ($operation instanceof DeleteOperationInterface) {
            return $this->removeProcessor->process($entity, $operation, $uriVariables, $context);
        }

        $this->persistProcessor->process($entity, $operation, $uriVariables, $context);
        if ($data->id === null) {
            $data->id = $entity->getId();
        }

        return $data;
    }

    private function mapDtoToEntity(object $dto): object
    {
        return $this->microMapper->map($dto, User::class);
    }
}
