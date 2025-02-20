<?php

namespace App\Factory;

use App\Entity\Notification;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Notification>
 */
final class NotificationFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Notification::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'dragonTreasure' => DragonTreasureFactory::new(),
            'message' => self::faker()->text(255),
        ];
    }
}
