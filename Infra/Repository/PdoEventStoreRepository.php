<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Repository;

use Astrotech\ApiBase\Adapter\Contracts\UuidGenerator;
use Astrotech\ApiBase\Domain\Contracts\DomainEvent;
use Astrotech\ApiBase\Domain\Contracts\EventStoreRepository;
use PDO;

final class PdoEventStoreRepository implements EventStoreRepository
{
    public function __construct(
        private readonly PDO $connection,
        private readonly UuidGenerator $uuidGenerator,
        private readonly string $tableName = 'event_store'
    ) {
    }

    public function store(DomainEvent $event): int | string
    {
        $this->createEventStoreTable();
        $id = $this->uuidGenerator->create();
        $event->setEventId($id);

        $values = [
            'id' => $this->uuidGenerator->toBytes($id),
            'user_id' => $event->userId(),
            'name' => $event->name(),
            'type' => $event->type(),
            'payload' => (string)$event,
            'created_at' => $event->when()->format('Y-m-d H:i:s'),
        ];

        $columns = [];
        $bindings = [];

        foreach ($values as $columnName => $value) {
            $columns[] = "`{$columnName}`";
            $bindings[] = ":{$columnName}";
        }

        $sql = "
            INSERT INTO `{$this->tableName}` (" . implode(',', $columns) . ")
            VALUES (" . implode(',', $bindings) . ")
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($values);
        $event->setEventId($id);

        return $id;
    }

    private function createEventStoreTable(): void
    {
        $checkTableQuery = "SHOW TABLES LIKE '{$this->tableName}'";
        $stmt = $this->connection->prepare($checkTableQuery);
        $stmt->execute();

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return;
        }

        $this->connection->exec("
            CREATE TABLE `{$this->tableName}` (
                `id` varbinary(16) NOT NULL,
                `user_id` VARCHAR(100) NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `type` VARCHAR(100) NOT NULL,
                `payload` TEXT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `processed_at` TIMESTAMP DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
        ");
    }
}
