<?php

namespace App\Validator;

use App\ApiResource\UserApi;
use App\Entity\User;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class IsValidOwnerValidator extends ConstraintValidator
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        assert($constraint instanceof IsValidOwner);

        if (!$value) {
            return;
        }

        $currentUser = $this->security->getUser();
        if ($currentUser === null) {
            throw new LogicException('IsOwnerValidator should only be used when a user is logged in.');
        }

        assert($value instanceof UserApi);
        assert($currentUser instanceof User);

        if (!$this->security->isGranted('ROLE_ADMIN') && $currentUser->getId() !== $value->id) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
