<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\Contracts;

interface EventBus
{
    public function dispatch(DomainEvent $event): void;
}
