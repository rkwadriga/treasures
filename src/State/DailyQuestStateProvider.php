<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\DailyQuest;
use App\Enum\DailyQuestStatusEnum;
use DateTimeImmutable;

class DailyQuestStateProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $quests = $this->createQuests();
        if ($operation instanceof CollectionOperationInterface) {
            return $quests;
        }

        if (isset($uriVariables['dayString'])) {
            return $quests[$uriVariables['dayString']] ?? null;
        }
    }

    private function createQuests(): array
    {
        $quests = [];
        for ($i = 0; $i < 50; $i++) {
            $quest = new DailyQuest(new DateTimeImmutable(sprintf('- %d days', $i)));
            $quest->name = sprintf('Quest %d', $i);
            $quest->description = sprintf('Description %d', $i);
            $quest->difficulty = $i % 10;
            $quest->status = $i % 2 === 0 ? DailyQuestStatusEnum::ACTIVE : DailyQuestStatusEnum::COMPLETED;
            $quest->lastUpdated = new DateTimeImmutable(sprintf('- %d days', rand(10, 100)));

            $quests[$quest->getDayString()] = $quest;
        }
        return $quests;
    }
}
