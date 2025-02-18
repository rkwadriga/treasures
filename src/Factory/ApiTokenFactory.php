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

    public static function createOneWithExpiresAfter(string $interval, array $attributes = []): ApiToken
    {
        $date = new DateTime();
        $date->add(DateInterval::createFromDateString("+{$interval}"));
        return self::createOne(array_merge([
            'expiresAt' => DateTimeImmutable::createFromMutable($date),
        ], $attributes));
    }

    public static function createOneWithScopes(array $scopes, array $attributes = []): ApiToken
    {
        return self::createOne(array_merge(['scopes' => $scopes], $attributes));
    }

    public static function createOneWithExpiresAfterAndScopes(string $interval, array $scopes, array $attributes = []): ApiToken
    {
        return self::createOneWithExpiresAfter($interval, array_merge(['scopes' => $scopes], $attributes));
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
