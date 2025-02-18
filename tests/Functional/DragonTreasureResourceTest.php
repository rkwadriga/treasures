<?php

namespace App\Tests\Functional;

use App\Entity\ApiToken;
use App\Factory\ApiTokenFactory;
use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

/**
 * Run tests: ./bin/phpunit tests/Functional/DragonTreasureResourceTest.php
 */
class DragonTreasureResourceTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    private string $baseUrl = '/api';

    /**
     * Run test: ./bin/phpunit --filter=testGetCollectionOfTreasures
     */
    public function testGetCollectionOfTreasures(): void
    {
        DragonTreasureFactory::createMany(5);

        $json = $this->browser()
            ->get("{$this->baseUrl}/treasures")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->json()
        ;

        $json
            ->assertMatches('totalItems', 5)
            ->assertMatches('keys(member[0])', [
            '@id',
            '@type',
            'name',
            'description',
            'value',
            'coolFactor',
            'owner',
            'shortDescription',
            'plunderedAtAgo',
        ]);
    }

    /**
     * Run test: ./bin/phpunit --filter=testPostToCreateTreasure
     */
    public function testPostToCreateTreasure(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->post("{$this->baseUrl}/treasures", [
                'json' => [],
            ])
            ->assertStatus(422)
            ->post("{$this->baseUrl}/treasures", HttpOptions::json([
                'name' => 'A shiny thing',
                'description' => 'It sparkles when I wave it in the air.',
                'value' => 1000,
                'coolFactor' => 5,
                'owner' => "{$this->baseUrl}/users/{$user->getId()}",
            ]))
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson()
            ->assertJsonMatches('name', 'A shiny thing')
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testPostToCreateTreasureWithApiToken
     */
    public function testPostToCreateTreasureWithApiToken(): void
    {
        $token = ApiTokenFactory::createOneWithExpiresAfterAndScopes('1 hour', [ApiToken::SCOPE_TREASURE_CREATE]);

        $this->browser()
            ->post("{$this->baseUrl}/treasures", [
                'json' => [],
                'headers' => [
                    'Authorization' => "Bearer {$token->getToken()}",
                ]
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testPostToCreateTreasureDeniedWithoutScope
     */
    public function testPostToCreateTreasureDeniedWithoutScope(): void
    {
        $token = ApiTokenFactory::createOneWithExpiresAfterAndScopes('1 hour', [ApiToken::SCOPE_TREASURE_EDIT]);

        $this->browser()
            ->post("{$this->baseUrl}/treasures", [
                'json' => [],
                'headers' => [
                    'Authorization' => "Bearer {$token->getToken()}",
                ]
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;
    }
}