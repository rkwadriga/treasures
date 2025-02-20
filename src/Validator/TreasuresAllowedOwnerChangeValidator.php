<?php

namespace App\Validator;

use App\Entity\DragonTreasure;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class TreasuresAllowedOwnerChangeValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        assert($constraint instanceof TreasuresAllowedOwnerChange);

        if (!$value) {
            return;
        }

        assert($value instanceof Collection);

        $isAdmin = $this->security->isGranted('ROLE_ADMIN');
        $unitOfWork = $this->entityManager->getUnitOfWork();
        foreach ($value as $dragonTreasure) {
            assert($dragonTreasure instanceof DragonTreasure);
            $originalData = $unitOfWork->getOriginalEntityData($dragonTreasure);
            $originalOwnerId = $originalData['owner_id'];
            $newOwnerId = $dragonTreasure->getOwner()->getId();
            if (!$originalOwnerId || $originalOwnerId === $newOwnerId || $isAdmin) {
                continue;
            }

            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
