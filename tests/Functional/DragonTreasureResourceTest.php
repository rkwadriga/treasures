<?php

namespace App\Tests\Functional;

use App\Entity\ApiToken;
use App\Entity\Notification;
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

    /**
     * Run test: ./bin/phpunit --filter=testGetCollectionOfTreasures
     */
    public function testGetCollectionOfTreasures(): void
    {
        DragonTreasureFactory::createMany(5, ['isPublished' => true]);
        DragonTreasureFactory::new()->asNotPublished()->create();

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
            'isMine',
            'shortDescription',
            'plunderedAtAgo',
        ]);
    }

    /**
     * Run test: ./bin/phpunit --filter=testAdminCanSeeUnpublishedTreasures
     */
    public function testAdminCanSeeUnpublishedTreasures(): void
    {
        $admin = UserFactory::new()->asAdmin()->create();
        DragonTreasureFactory::createMany(5, ['isPublished' => true]);
        DragonTreasureFactory::new()->asNotPublished()->create();

        $this->browser()
            ->actingAs($admin)
            ->get("{$this->baseUrl}/treasures")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('totalItems', 6)
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testCanSeeUnpublishedTreasuresWithAdminToken
     */
    public function testCanSeeUnpublishedTreasuresWithAdminToken(): void
    {
        $token = ApiTokenFactory::new()->withScopes(['ROLE_ADMIN'])->asNotExpired()->create();
        DragonTreasureFactory::createMany(5, ['isPublished' => true]);
        DragonTreasureFactory::new()->asNotPublished()->create();

        $this->browser()
            ->get("{$this->baseUrl}/treasures", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken(),
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('totalItems', 6)
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testGetOneUnpublishedTreasure404s
     */
    public function testGetOneUnpublishedTreasure404s(): void
    {
        $treasure = DragonTreasureFactory::new()->asNotPublished()->create();

        $this->browser()
            ->get("{$this->baseUrl}/treasures/{$treasure->getId()}")
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testAdminCanSeeSingleUnpublishedTreasure
     */
    public function testAdminCanSeeSingleUnpublishedTreasure(): void
    {
        $admin = UserFactory::new()->asAdmin()->create();
        $treasure = DragonTreasureFactory::new()
            ->asNotPublished()
            ->withName('Super valuable treasure')
            ->create()
        ;

        $this->browser()
            ->actingAs($admin)
            ->get("{$this->baseUrl}/treasures/{$treasure->getId()}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('name', 'Super valuable treasure')
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testCanSeeSingleUnpublishedTreasureWithAdminToken
     */
    public function testCanSeeSingleUnpublishedTreasureWithAdminToken(): void
    {
        $token = ApiTokenFactory::new()->withScopes(['ROLE_ADMIN'])->asNotExpired()->create();
        $treasure = DragonTreasureFactory::new()
            ->asNotPublished()
            ->withName('Super valuable treasure')
            ->create()
        ;

        $this->browser()
            ->get("{$this->baseUrl}/treasures/{$treasure->getId()}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken(),
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('name', 'Super valuable treasure')
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testOwnerCanSeeUnpublishedTreasures
     */
    public function testOwnerCanSeeUnpublishedTreasures(): void
    {
        $user = UserFactory::createOne();
        DragonTreasureFactory::createMany(5, ['isPublished' => true]);
        DragonTreasureFactory::new()->withOwner($user)->asNotPublished()->create();

        $this->browser()
            ->actingAs($user)
            ->get("{$this->baseUrl}/treasures")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('totalItems', 6)
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testUnpublishedTreasuresVisibleWithOwnersToken
     */
    public function testUnpublishedTreasuresVisibleWithOwnersToken(): void
    {
        $user = UserFactory::createOne();
        $token = ApiTokenFactory::new()->withOwnedBy($user)->asNotExpired()->create();
        DragonTreasureFactory::createMany(5, ['isPublished' => true]);
        DragonTreasureFactory::new()->withOwner($user)->asNotPublished()->create();

        $this->browser()
            ->get("{$this->baseUrl}/treasures", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken(),
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('totalItems', 6)
        ;
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
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->post("{$this->baseUrl}/treasures", HttpOptions::json([
                'name' => 'A shiny thing',
                'description' => 'It sparkles when I wave it in the air.',
                'value' => 1000,
                'coolFactor' => 5,
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
        $token = ApiTokenFactory::new()->asNotExpired()->withScopes([ApiToken::SCOPE_TREASURE_CREATE])->create();

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
        $token = ApiTokenFactory::new()->asNotExpired()->withScopes([ApiToken::SCOPE_TREASURE_EDIT])->create();

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

    /**
     * Run test: ./bin/phpunit --filter=testPatchToUpdateTreasure
     */
    public function testPatchToUpdateTreasure(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::new()
            ->withValue(111111)
            ->withOwner($user)
            ->asPublished()
            ->create()
        ;

        $this->browser()
            ->actingAs($user)
            ->patch("{$this->baseUrl}/treasures/{$treasure->getId()}", [
                'json' => [
                    'value' => 1234578,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('value', 1234578)
        ;

        $user2 = UserFactory::createOne();
        $this->browser()
            ->actingAs($user2)
            ->patch("{$this->baseUrl}/treasures/{$treasure->getId()}", [
                'json' => [
                    'value' => 8765421,
                ],
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;

        $this->browser()
            ->actingAs($user)
            ->patch("{$this->baseUrl}/treasures/{$treasure->getId()}", [
                'json' => [
                    'owner' => "{$this->baseUrl}/users/{$user2->getId()}",
                ],
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testPatchUnpublishedTreasure
     */
    public function testPatchUnpublishedTreasure(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::new()
            ->withValue(111111)
            ->withOwner($user)
            ->asNotPublished()
            ->create()
        ;

        $this->browser()
            ->actingAs($user)
            ->patch("{$this->baseUrl}/treasures/{$treasure->getId()}", [
                'json' => [
                    'value' => 1234578,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('value', 1234578)
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testAdminCanPatchToUpdateTreasure
     */
    public function testAdminCanPatchToUpdateTreasure(): void
    {
        $admin = UserFactory::new()->asAdmin()->create();
        $treasure = DragonTreasureFactory::new()->withValue(111111)->asNotPublished()->create();

        $this->browser()
            ->actingAs($admin)
            ->patch("{$this->baseUrl}/treasures/{$treasure->getId()}", [
                'json' => [
                    'value' => 12345678,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('value', 12345678)
            ->assertJsonMatches('isPublished', false)
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
     */
    public function testOwnerCanSeeIsPublishedAndIsMineFields(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::new()->withOwner($user)->asPublished()->withValue(12345)->create();

        $this->browser()
            ->actingAs($user)
            ->patch("{$this->baseUrl}/treasures/{$treasure->getId()}", [
                'json' => [
                    'value' => 54321,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('value', 54321)
            ->assertJsonMatches('isPublished', true)
            ->assertJsonMatches('isMine', true)
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testOwnerCanSeeIsPublishedField
     */
    public function testOwnerCanSeeIsPublishedField(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::new()->withOwner($user)->asPublished()->create();

        $this->browser()
            ->actingAs($user)
            ->patch("{$this->baseUrl}/treasures/{$treasure->getId()}", [
                'json' => [
                    'value' => 12345678,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('value', 12345678)
            ->assertJsonMatches('isPublished', true)
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testImpossibleGetTreasuresCollectionWithExpiredToken
     */
    public function testImpossibleGetTreasuresCollectionWithExpiredToken(): void
    {
        $token = ApiTokenFactory::new()->asExpired()->create();
        DragonTreasureFactory::new()->asPublished()->create();

        $this->browser()
            ->get("{$this->baseUrl}/treasures", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken(),
                ],
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;
    }

    /**
     * Run test: ./bin/phpunit --filter=testPublishTreasure
     */
    public function testPublishTreasure()
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::new()->withOwner($user)->asNotPublished()->create();

        $this->browser()
            ->actingAs($user)
            ->patch("{$this->baseUrl}/treasures/{$treasure->getId()}", [
                'json' => [
                    'isPublished' => true,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('isPublished', true)
        ;

        $notificationRepository = $this->getRepository(Notification::class);
        $this->assertEquals(1, $notificationRepository->count());
    }
}