<?php

namespace App\Security\Voter;

use App\Entity\ApiToken;
use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class DragonTreasureVoter extends Voter
{
    public const string EDIT = 'EDIT';

    public function __construct(
        private readonly Security $security
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT && $subject instanceof DragonTreasure;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        assert($subject instanceof DragonTreasure);

        if (!$this->security->isGranted(ApiToken::SCOPE_TREASURE_EDIT)) {
            return false;
        }

        if ($subject->getOwner() === $user || $this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }
}
