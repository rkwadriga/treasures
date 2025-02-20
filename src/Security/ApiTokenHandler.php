<?php

namespace App\Security;

use App\Repository\ApiTokenRepository;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

readonly class ApiTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private ApiTokenRepository $tokenRepository,
    ) {
    }

    public function getUserBadgeFrom(#[SensitiveParameter] string $accessToken): UserBadge
    {
        $token = $this->tokenRepository->findOneBy(['token' => $accessToken]);
        if ($token === null) {
            throw new BadCredentialsException();
        }
        if (!$token->isValid()) {
            throw new CustomUserMessageAuthenticationException('Token expired');
        }

        // Set user's roles to be defined by token's scopes
        $token->getOwnedBy()->setAccessTokenScopes($token->getScopes());

        return new UserBadge($token->getOwnedBy()->getUserIdentifier());
    }
}