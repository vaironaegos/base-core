<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\Collection;

use DateTimeInterface;
use Astrotech\ApiBase\Utils\CollectionBase;

final class DatetimeCollection extends CollectionBase
{
    protected function className(): string
    {
        return DateTimeInterface::class;
    }
}
