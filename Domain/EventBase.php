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
        return [
            'name' => self::name(),
            'className' => get_called_class(),
            'when' => $this->when()->format(DATE_ATOM),
            'data' => get_object_vars($this)
        ];
    }

    public static function name(): string
    {
        $className = get_called_class();
        return basename(str_replace('\\', '/', $className));
    }
}
