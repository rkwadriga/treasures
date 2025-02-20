<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UserApi;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class EntityClassDtoStateProcessor implements ProcessorInterface
{
    public function __construct(
        private UserRepository                                                   $userRepository,
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $persistProcessor,
        #[Autowire(service: RemoveProcessor::class)] private ProcessorInterface  $removeProcessor,
        private UserPasswordHasherInterface                                      $passwordHasher,
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
        assert($dto instanceof UserApi);
        if ($dto->id !== null) {
            $entity = $this->userRepository->find($dto->id);
            if ($entity === null) {
                throw new EntityNotFoundException("Entity #{$dto->id} not found");
            }
        } else {
            $entity = new User();
        }

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
