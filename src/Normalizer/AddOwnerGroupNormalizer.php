<?php

namespace App\Normalizer;

use App\Entity\DragonTreasure;
use ArrayObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsDecorator('api_platform.jsonld.normalizer.item')]
readonly class AddOwnerGroupNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    public function __construct(
        private NormalizerInterface $innerNormalizer,
        private Security $security
    ) {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (isset($context['groups']) && $data instanceof DragonTreasure && $this->security->getUser() === $data->getOwner()) {
            $context['groups'][] = 'owner:read';
        }

        return $this->innerNormalizer->normalize($data, $format, $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->innerNormalizer->supportsNormalization($data, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            'object' => null,
            '*' => false,
            DragonTreasure::class => true
        ];
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        if ($this->innerNormalizer instanceof SerializerAwareInterface) {
            $this->innerNormalizer->setSerializer($serializer);
        }
    }
}