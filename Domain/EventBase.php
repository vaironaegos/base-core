<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain;

use DateTimeImmutable;
use Astrotech\ApiBase\Domain\Contracts\Event;

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
