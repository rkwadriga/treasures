<?php

namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Run tests: ./bin/phpunit tests/Functional/UserResourceTest.php
 */
class UserResourceTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    /**
     * Run tests: ./bin/phpunit --filter=testPostToCreateUser
     */
    public function testPostToCreateUser(): void
    {
        $this->browser()
            ->post("{$this->baseUrl}/users", [
                'json' => [
                    'email' => 'draggin_in_the_morning@coffee.com',
                    'username' => 'draggin_in_the_morning',
                    'password' => 'password',
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->post('/login', [
                'json' => [
                    'email' => 'draggin_in_the_morning@coffee.com',
                    'password' => 'password',
                ]
            ])
            ->assertSuccessful()
        ;
    }

    /**
     * Run tests: ./bin/phpunit --filter=testPatchToUpdateUser
     */
    public function testPatchToUpdateUser(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->patch("{$this->baseUrl}/users/{$user->getId()}", [
                'json' => [
                    'username' => 'changed_username',
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ]
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('username', 'changed_username')
        ;
    }

    /**
     * Run tests: ./bin/phpunit --filter=testTreasureCanNotBeStolen
     */
    public function testTreasureCanNotBeStolen(): void
    {
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();
        $treasure = DragonTreasureFactory::new()
            ->withOwner($otherUser)
            ->asPublished()
            ->create()
        ;

        $this->browser()
            ->actingAs($user)
            ->patch("{$this->baseUrl}/users/{$user->getId()}", [
                'json' => [
                    'username' => 'changed_username',
                    'dragonTreasures' => [
                        "{$this->baseUrl}/treasures/{$treasure->getId()}"
                    ],
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ]
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;
    }

    /**
     * Run tests: ./bin/phpunit --filter=testAdminCanChangeTreasureOwner
     */
    public function testAdminCanChangeTreasureOwner(): void
    {
        $admin = UserFactory::new()->asAdmin()->create();
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();
        $treasure = DragonTreasureFactory::new()->withOwner($otherUser)->create();

        $this->browser()
            ->actingAs($admin)
            ->patch("{$this->baseUrl}/users/{$user->getId()}", [
                'json' => [
                    'username' => 'changed_username',
                    'dragonTreasures' => [
                        "{$this->baseUrl}/treasures/{$treasure->getId()}"
                    ],
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ]
            ])
            ->assertStatus(Response::HTTP_OK)
        ;
    }

    /**
     * Run tests: ./bin/phpunit --filter=testUnpublishedTreasuresTonReturned
     */
    public function testUnpublishedTreasuresTonReturned(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::new()->withOwner($user)->asNotPublished()->create();

        $this->browser()
            ->actingAs(UserFactory::createOne())
            ->get("{$this->baseUrl}/users/{$user->getId()}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('length("dragonTreasures")', 0)
        ;
    }
}