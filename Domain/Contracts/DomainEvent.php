<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Contracts;

use DateTimeImmutable;
use DateTimeInterface;

interface DomainEvent
{
    public static function name(): string;

    public function when(): DateTimeImmutable;

    public function values(): array;

    public function setAction(string $actionName): void;

    public function addData(string $key, mixed $value): void;
}
