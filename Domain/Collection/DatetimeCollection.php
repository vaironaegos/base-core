<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Collection;

use DateTimeInterface;
use Astrotech\Core\Base\Utils\CollectionBase;

final class DatetimeCollection extends CollectionBase
{
    protected function className(): string
    {
        return DateTimeInterface::class;
    }
}
