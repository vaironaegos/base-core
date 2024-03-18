<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\CommandBus;

use Astrotech\Core\Base\Domain\Contracts\EventBus;
use Astrotech\Core\Base\Domain\Contracts\DomainEvent;
use Astrotech\Core\Base\Domain\Contracts\CommandHandler;
use Astrotech\Core\Base\Domain\Contracts\EventStoreRepository;

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
        if (!is_null($this->eventStoreRepo)) {
            $this->eventStoreRepo->store($event);
        }

        $this->eventBus->dispatch($event);
    }
}
