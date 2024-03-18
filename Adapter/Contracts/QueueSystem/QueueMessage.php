<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts\QueueSystem;

use Astrotech\Core\Base\Adapter\DtoBase;

final class QueueMessage extends DtoBase
{
    public function __construct(
        public readonly string $queueName,
        public readonly QueueActions | string $action,
        public readonly array $data,
        protected readonly array $options = []
    ) {
    }

    public function getOption(string|int $optionKey, mixed $defaultValue = null): mixed
    {
        if (!isset($this->options[$optionKey])) {
            return $defaultValue;
        }

        return $this->options[$optionKey];
    }

    public function __toString(): string
    {
        $dataCloned = $this->data;
        $actionName = is_string($this->action) ? $this->action : $this->action->value;
        $eventId = $dataCloned['eventId'] ?? null;
        $processed = $dataCloned['processed'] ?? null;
        $createdAt = $dataCloned['createdAt'] ?? null;
        $eventName = $dataCloned['eventName'] ?? null;
        $userId = $dataCloned['userId'] ?? null;

        unset(
            $dataCloned['eventId'],
            $dataCloned['processed'],
            $dataCloned['eventName'],
            $dataCloned['userId']
        );

        return json_encode([
            'eventId' => $eventId,
            'userId' => $userId,
            'processed' => $processed,
            'action' => $actionName,
            'createdAt' => $createdAt,
            'eventName' => $eventName,
            'data' => $dataCloned
        ]);
    }
}
