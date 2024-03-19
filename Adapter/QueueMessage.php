<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter;

final class QueueMessage extends DtoBase
{
    public function __construct(
        public readonly string $queueName,
        public readonly string $actionName,
        public readonly array $payload,
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
        $dataCloned = $this->payload;
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
            'action' => $this->actionName,
            'createdAt' => $createdAt,
            'eventName' => $eventName,
            'data' => $dataCloned
        ]);
    }
}
