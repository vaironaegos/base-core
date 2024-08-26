<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\CommandBus;

use Astrotech\Core\Base\Domain\Contracts\EventBus;
use Astrotech\Core\Base\Domain\Contracts\DomainEvent;
use Astrotech\Core\Base\Domain\Contracts\CommandHandler;

abstract class CommandHandlerBase implements CommandHandler
{
    protected EventBus $eventBus;

    public function setEventBus(EventBus $eventBus): void
    {
        $this->eventBus = $eventBus;
    }

    public function dispatchEvent(DomainEvent $event): void
    {
        $this->eventBus->dispatch($event);
    }
}
