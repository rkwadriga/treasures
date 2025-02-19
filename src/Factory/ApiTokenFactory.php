<?php

namespace App\Factory;

use App\Entity\ApiToken;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ApiToken>
 */
final class ApiTokenFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return ApiToken::class;
    }

    public function withExpiresAfter(string $interval): ApiTokenFactory
    {
        $date = new DateTime();
        $date->add(DateInterval::createFromDateString("+{$interval}"));
        return $this->withExpiresAt($date);
    }

    public function withExpiresAt(DateTimeImmutable|DateTime|string $expiresAt): ApiTokenFactory
    {
        if (is_string($expiresAt)) {
            $expiresAt = new DateTimeImmutable($expiresAt);
        } elseif (!$expiresAt instanceof DateTimeImmutable) {
            $expiresAt = DateTimeImmutable::createFromMutable($expiresAt);
        }

        return $this->with(['expiresAt' => $expiresAt]);
    }

    public function withScopes(array $scopes): ApiTokenFactory
    {
        return $this->with(['scopes' => $scopes]);
    }

    public function asExpired(string $interval = '1 hour'): ApiTokenFactory
    {
        $date = new DateTime();
        $date->add(DateInterval::createFromDateString("-{$interval}"));
        return $this->withExpiresAt($date);
    }

    protected function defaults(): array|callable
    {
        $monthsCount = self::faker()->numberBetween(0, 12);
        $date = self::faker()->dateTimeThisMonth()->add(DateInterval::createFromDateString("+{$monthsCount} months"));
        return [
            'expiresAt' => DateTimeImmutable::createFromMutable($date),
            'ownedBy' => UserFactory::new(),
            'scopes' => [
                ApiToken::SCOPE_TREASURE_CREATE,
                ApiToken::SCOPE_TREASURE_EDIT,
            ],
        ];
    }
}
