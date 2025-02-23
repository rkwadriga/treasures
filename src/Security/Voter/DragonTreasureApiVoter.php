<?php

namespace App\Security\Voter;

use App\ApiResource\DragonTreasureApi;
use App\Entity\ApiToken;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class DragonTreasureApiVoter extends Voter
{
    public const string EDIT = 'EDIT';

    public function __construct(
        private readonly Security $security
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT && $subject instanceof DragonTreasureApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        assert($subject instanceof DragonTreasureApi);

        if (!$this->security->isGranted(ApiToken::SCOPE_TREASURE_EDIT)) {
            return false;
        }

        return $subject->owner?->id === $user->getId();
    }
}
