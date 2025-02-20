<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\DailyQuest;
use DateTimeImmutable;

class DailyQuestStateProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        assert($data instanceof DailyQuest);
        $data->lastUpdated = new DateTimeImmutable('now');

        return $data;
    }
}
