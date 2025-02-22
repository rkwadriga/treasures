<?php

namespace App\Factory;

use App\Entity\DragonTreasure;
use App\Entity\User;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<DragonTreasure>
 */
final class DragonTreasureFactory extends PersistentProxyObjectFactory
{
    private const array TREASURE_NAMES = ['pile of gold coins', 'diamond-encrusted throne', 'rare magic staff', 'enchanted sword', 'set of intricately crafted goblets', 'collection of ancient tomes', 'hoard of shiny gemstones', 'chest filled with priceless works of art', 'giant pearl', 'crown made of pure platinum', 'giant egg (possibly a dragon egg?)', 'set of ornate armor', 'set of golden utensils', 'statue carved from a single block of marble', 'collection of rare, antique weapons', 'box of rare, exotic chocolates', 'set of ornate jewelry', 'set of rare, antique books', 'giant ball of yarn', 'life-sized statue of the dragon itself', 'collection of old, used toothbrushes', 'box of mismatched socks', 'set of outdated electronics (such as CRT TVs or floppy disks)', 'giant jar of pickles', 'collection of novelty mugs with silly sayings', 'pile of old board games', 'giant slinky', 'collection of rare, exotic hats'];

    public function withValue(int $value): DragonTreasureFactory
    {
        return $this->with(['value' => $value]);
    }

    public function withOwner(User $owner): DragonTreasureFactory
    {
        return $this->with(['owner' => $owner]);
    }

    public function withIsPublished(bool $isPublished): DragonTreasureFactory
    {
        return $this->with(['isPublished' => $isPublished]);
    }

    public function withName(string $name): DragonTreasureFactory
    {
        return $this->with(['name' => $name]);
    }

    public function asPublished(): DragonTreasureFactory
    {
        return $this->withIsPublished(true);
    }

    public function asNotPublished(): DragonTreasureFactory
    {
        return $this->withIsPublished(false);
    }

    public static function class(): string
    {
        return DragonTreasure::class;
    }

    protected function defaults(): array|callable
    {
        $name = self::faker()->randomElement(self::TREASURE_NAMES);

        return [
            'coolFactor' => self::faker()->numberBetween(1, 10),
            'description' => self::faker()->paragraph(),
            'isPublished' => self::faker()->boolean(),
            'name' => $name,
            'plunderedAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-1 year')),
            'value' => self::faker()->numberBetween(1000, 1000000),
            'owner' => UserFactory::new(),
        ];
    }
}
