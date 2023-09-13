<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\Contracts;

use DateTimeImmutable;
use DateTimeInterface;

interface DomainEvent
{
    public function name(): string;
    public function type(): string;
    public function when(): DateTimeImmutable;
    public function values(): array;
    public function processedAt(): ?DateTimeInterface;
    public function wasProcessed(): bool;
    public function userId(): string | int;
    public function setEventId(string $eventId): void;
    public function setUserId(string $userId): void;
}
