<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\DailyQuest;
use App\ApiResource\QuestTreasure;
use App\Enum\DailyQuestStatusEnum;
use App\Repository\DragonTreasureRepository;
use ArrayIterator;
use DateTimeImmutable;

class DailyQuestStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly DragonTreasureRepository $treasureRepository,
        private readonly Pagination $pagination,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $quests = $this->createQuests();
        if ($operation instanceof CollectionOperationInterface) {
            $currentPage = $this->pagination->getPage($context);
            $itemsPerPage = $this->pagination->getLimit($operation, $context);
            $offset = $this->pagination->getOffset($operation, $context);
            $totalItemsCount = $this->getTotalItemsCount();

            return new TraversablePaginator(
                new ArrayIterator(array_slice($quests, $offset, $itemsPerPage)),
                $currentPage,
                $itemsPerPage,
                $totalItemsCount
            );
        }

        if (isset($uriVariables['dayString'])) {
            return $quests[$uriVariables['dayString']] ?? null;
        }
    }

    private function createQuests(): array
    {
        $treasures = $this->treasureRepository->findBy([], [], 10);

        $quests = [];
        for ($i = 0; $i < $this->getTotalItemsCount(); $i++) {
            $quest = new DailyQuest(new DateTimeImmutable(sprintf('- %d days', $i)));
            $quest->name = sprintf('Quest %d', $i);
            $quest->description = sprintf('Description %d', $i);
            $quest->difficulty = $i % 10;
            $quest->status = $i % 2 === 0 ? DailyQuestStatusEnum::ACTIVE : DailyQuestStatusEnum::COMPLETED;
            $quest->lastUpdated = new DateTimeImmutable(sprintf('- %d days', rand(10, 100)));

            $quest->treasures = [];
            if (!empty($treasures)) {
                $randomTreasuresKeys = array_rand($treasures, rand(1, 3));
                $randomTreasures = array_map(fn($key) => $treasures[$key], (array) $randomTreasuresKeys);
                foreach ($randomTreasures as $treasure) {
                    $quest->treasures[] = new QuestTreasure(
                        $treasure->getName(),
                        $treasure->getValue(),
                        $treasure->getCoolFactor()
                    );
                }
            }


            $quests[$quest->getDayString()] = $quest;
        }
        return $quests;
    }

    private function getTotalItemsCount(): int
    {
        return 53;
    }
}
