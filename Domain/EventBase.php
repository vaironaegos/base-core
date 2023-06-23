<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain;

use Astrotech\ApiBase\Domain\Contracts\Event;
use DateTimeImmutable;

abstract class EventBase implements Event
{
    public function when(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function values(): array
    {
        return get_object_vars($this);
    }
}
