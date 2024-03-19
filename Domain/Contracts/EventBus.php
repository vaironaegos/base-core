<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Contracts;

interface EventBus
{
    public function dispatch(DomainEvent $event): void;
}
