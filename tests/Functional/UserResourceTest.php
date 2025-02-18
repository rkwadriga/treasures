<?php

namespace App\Tests\Functional;

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
}