<?php

namespace App\ApiResource;

use AllowDynamicProperties;
use ApiPlatform\Metadata;
use App\Enum\DailyQuestStatusEnum;
use App\State\DailyQuestStateProcessor;
use App\State\DailyQuestStateProvider;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation;

#[Metadata\ApiResource(
    shortName: 'Quest',
    operations: [
        new Metadata\GetCollection(),
        new Metadata\Get(),
        new Metadata\Patch(
            processor: DailyQuestStateProcessor::class // It's normal to move processor tp the ApiResource config because the GET requests never call processors
        )
    ],
    provider: DailyQuestStateProvider::class
)]
class DailyQuest
{
    #[Annotation\Ignore]
    public DateTimeInterface $day;

    public string $name;

    public string $description;

    public int $difficulty;

    public DailyQuestStatusEnum $status;

    public DateTimeInterface $lastUpdated;

    public function __construct(DateTimeInterface $day)
    {
        $this->day = $day;
    }

    #[Metadata\ApiProperty(readable: false, identifier: true)]
    #[Annotation\SerializedName('day')]
    public function getDayString(): string
    {
        return $this->day->format('Y-m-d');
    }
}