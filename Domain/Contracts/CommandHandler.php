<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\Contracts;

use Astrotech\ApiBase\Adapter\Contracts\Dto;

interface CommandHandler
{
    public function handle(Dto $command);
    public function setEventBus(EventBus $eventBus): void;
    public function dispatchEvent(DomainEvent $event): void;
    public function setEventStoreRepo(EventStoreRepository $eventStoreRepository = null): void;
}
