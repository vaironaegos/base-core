<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\Collection;

use Astrotech\ApiBase\Utils\CollectionBase;
use DateTimeInterface;

final class DatetimeCollection extends CollectionBase
{
    protected function className(): string
    {
        return DateTimeInterface::class;
    }
}
