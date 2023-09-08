<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Repository;

use Astrotech\ApiBase\Domain\Contracts\DomainEvent;
use Astrotech\ApiBase\Domain\Contracts\EventStoreRepository;
use PDO;

final class PdoEventStoreRepository implements EventStoreRepository
{
    public function __construct(
        private readonly PDO $connection
    ) {
    }

    public function store(DomainEvent $event): int | string
    {
        $this->createEventStoreTable();
        return 'a';
    }

    private function createEventStoreTable()
    {
        // SQL TO CREATE TABLE
    }
}
