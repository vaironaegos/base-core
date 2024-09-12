<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Contracts;

use DateTimeImmutable;

interface Event
{
    public static function name(): string;
    public function when(): DateTimeImmutable;
    public function values(): array;
}
