<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain;

use Illuminate\Support\Facades\App;
use Astrotech\ApiBase\Domain\Contracts\Event;
use Psr\EventDispatcher\EventDispatcherInterface;
use Astrotech\ApiBase\Exception\RuntimeException;
use Psr\EventDispatcher\ListenerProviderInterface;

final class DomainEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private readonly ListenerProviderInterface $listenerProvider
    ) {
    }

    /**
     * @param object<Event> $event
     * @throws RuntimeException
     */
    public function dispatch(object $event): void
    {
        foreach ($this->listenerProvider->getListenersForEvent($event) as $className) {
            $listenerObject = App::make($className);

            if (!method_exists($listenerObject, 'handle')) {
                throw new RuntimeException("Listeners class doesn't have 'handle' method");
            }

            call_user_func_array([$listenerObject, 'handle'], [$event]);
        }
    }
}
