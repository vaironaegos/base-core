<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\EventBus;

use Stringable;
use JsonSerializable;
use DateTimeImmutable;
use DateTimeInterface;
use Astrotech\ApiBase\Domain\Contracts\DomainEvent;

abstract class DomainEventBase implements DomainEvent, JsonSerializable, Stringable
{
    private string $eventId = '';
    private string $userId = '';
    private ?DateTimeInterface $processedAt = null;

    public function when(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function values(): array
    {
        return [
            ...get_object_vars($this),
            'createdAt' => $this->when()->format(DATE_ATOM),
            'eventName' => $this->name()
        ];
    }

    public function name(): string
    {
        return get_called_class();
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function processedAt(): ?DateTimeInterface
    {
        return $this->processedAt;
    }

    public function wasProcessed(): bool
    {
        return !is_null($this->processedAt);
    }

    public function setEventId(string $eventId): void
    {
        if (!empty($this->eventId)) {
            return;
        }

        $this->eventId = $eventId;
    }

    public function setUserId(string $userId): void
    {
        if (!empty($this->userId)) {
            return;
        }

        $this->userId = $userId;
    }

    public function jsonSerialize(): array
    {
        return $this->values();
    }

    public function __toString(): string
    {
        return json_encode($this->values());
    }
}
