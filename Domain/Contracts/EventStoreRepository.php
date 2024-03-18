<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Contracts;

interface EventStoreRepository
{
    public function store(DomainEvent $event): int | string;
}
