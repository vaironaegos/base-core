<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\CommandBus;

use Astrotech\ApiBase\Domain\Contracts\CommandHandler;
use Astrotech\ApiBase\Domain\Contracts\DomainEvent;
use Astrotech\ApiBase\Domain\Contracts\EventBus;
use Astrotech\ApiBase\Domain\Contracts\EventStoreRepository;

abstract class CommandHandlerBase implements CommandHandler
{
    protected EventBus $eventBus;
    protected ?EventStoreRepository $eventStoreRepo = null;

    public function setEventBus(EventBus $eventBus): void
    {
        $this->eventBus = $eventBus;
    }

    public function setEventStoreRepo(EventStoreRepository $eventStoreRepository = null): void
    {
        $this->eventStoreRepo = $eventStoreRepository;
    }

    public function dispatchEvent(DomainEvent $event): void
    {
        $this->eventBus->dispatch($event);

        if (is_null($this->eventStoreRepo)) {
            return;
        }

        $eventId = $this->eventStoreRepo->store($event);
        $event->setEventId($eventId);
    }
}
