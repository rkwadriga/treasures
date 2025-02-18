<?php

namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

/**
 * Run tests: ./bin/phpunit tests/Functional/DragonTreasureResourceTest.php
 */
class DragonTreasureResourceTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;
    use Factories;

    private string $baseUrl = '/api';


    public function testGetCollectionOfTreasures()
    {
        DragonTreasureFactory::createMany(5);

        $json = $this->browser()
            ->get("{$this->baseUrl}/treasures")
            ->assertStatus(200)
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
}