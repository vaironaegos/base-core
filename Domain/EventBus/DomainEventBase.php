<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\EventBus;

use Astrotech\Core\Base\Domain\Contracts\DomainEvent;
use DateTimeImmutable;

abstract class DomainEventBase implements DomainEvent
{
    private string $eventId = '';
    private string $userId = '';
    private string $action = '';
    private bool $processed = false;

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

    public static function name(): string
    {
        return get_called_class();
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
}
