<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain;

use Astrotech\ApiBase\Domain\Contracts\Event;
use Psr\EventDispatcher\ListenerProviderInterface;

final class DomainListenerProvider implements ListenerProviderInterface
{
    private array $listeners = [];

    /**
     * @param object<Event> $event
     * @return iterable
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventName = $event->name();
        if (array_key_exists($eventName, $this->listeners)) {
            return $this->listeners[$eventName];
        }

        return [];
    }
}
