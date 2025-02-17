<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Run tests: ./bin/phpunit DragonTreasureResourceTest
 */
class DragonTreasureResourceTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    private string $baseUrl = '/api';


    public function testGetCollectionOfTreasures()
    {
        $this->browser()
            ->get("{$this->baseUrl}/treasures")
            ->assertStatus(200)
            ->assertJson();
    }
}