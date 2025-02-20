<?php

namespace App\Tests\Functional;

use App\Enum\DailyQuestStatusEnum;
use DateTime;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Run tests: ./bin/phpunit tests/Functional/DailyQuestResourceTest.php
 */
class DailyQuestResourceTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    /**
     * Run test: ./bin/phpunit --filter=testPatchCanUpdateStatus
     */
    public function testPatchCanUpdateStatus(): void
    {
        $this->browser()
            ->patch($this->getUrl('-2 days'), [
                'json' => [
                    'status' => DailyQuestStatusEnum::COMPLETED,
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ]
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('status', DailyQuestStatusEnum::COMPLETED->value)
        ;
    }

    private function getUrl(DateTimeInterface|string|null $day = null): string
    {
        if (is_string($day)) {
            $day = new DateTime($day);
        }
        $url = $this->baseUrl . '/quests';
        return $day === null ? $url : $url . '/' . $day->format('Y-m-d');
    }
}