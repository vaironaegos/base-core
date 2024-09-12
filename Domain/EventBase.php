<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain;

use DateTimeImmutable;
use Astrotech\Core\Base\Domain\Contracts\Event;

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
