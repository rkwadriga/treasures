<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    const array USERNAMES = [
        'FlamingInferno',
        'ScaleSorcerer',
        'TheDragonWithBadBreath',
        'BurnedOut',
        'ForgotMyOwnName',
        'ClumsyClaws',
        'HoarderOfUselessTrinkets',
    ];

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    public function withRoles(array $roles): UserFactory
    {
        return $this->with(['roles' => $roles]);
    }

    public function asAdmin(): UserFactory
    {
        return $this->withRoles(['ROLE_ADMIN']);
    }

    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->email(),
            'password' => '12345678',
            'username' => self::faker()->randomElement(self::USERNAMES) . self::faker()->randomNumber(3),
        ];
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate(function(User $user): void {
            $user->setPassword($this->passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            ));
        });
    }
}
