<?php

namespace App\ApiResource;

use ApiPlatform\Metadata;
use App\Enum\DailyQuestStatusEnum;
use App\State\DailyQuestStateProvider;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation;

#[Metadata\ApiResource(
    shortName: 'Quest',
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