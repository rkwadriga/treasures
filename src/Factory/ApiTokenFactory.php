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
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return ApiToken::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
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

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ApiToken $apiToken): void {})
        ;
    }
}
