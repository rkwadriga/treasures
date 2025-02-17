<?php

namespace App\Factory;

use App\Entity\ApiToken;
use DateInterval;
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
