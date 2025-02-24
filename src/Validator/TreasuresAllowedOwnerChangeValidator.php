<?php

namespace App\Validator;

use App\ApiResource\UserApi;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class TreasuresAllowedOwnerChangeValidator extends ConstraintValidator
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof UserApi || $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        assert($constraint instanceof TreasuresAllowedOwnerChange);

        $isValid = true;
        foreach ($value->dragonTreasures as $treasureApi) {
            $originalOwnerId = $treasureApi->owner?->id;
            $newOwnerId = $value->id;
            if ($originalOwnerId !== null && $originalOwnerId !== $newOwnerId) {
                $isValid = false;
                break;
            }
        }

        if (!$isValid) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
