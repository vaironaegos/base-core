<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Collection;

use DateTimeInterface;

final class DatetimeCollection extends CollectionBase
{
    protected function className(): string
    {
        return DateTimeInterface::class;
    }
}
