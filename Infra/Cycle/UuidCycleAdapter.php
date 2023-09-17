<?php

namespace Astrotech\ApiBase\Infra\Cycle;

use Astrotech\ApiBase\Adapter\Contracts\UuidGenerator;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Injection\ValueInterface;
use Ramsey\Uuid\Uuid;

class UuidCycleAdapter implements ValueInterface
{
    private static UuidGenerator $uuid;
    private static string $id = '';

    public function rawValue(): string
    {
        return self::$uuid->toBytes(self::$id);
    }

    public function rawType(): int
    {
        return \PDO::PARAM_LOB;
    }

    public function __toString()
    {
        return self::$id;
    }

    public static function typecast($value, DatabaseInterface $db): string
    {
        if (isUuidString($value)) {
            self::$id = Uuid::fromString($value)->getBytes();
            return Uuid::fromString($value)->getBytes();
        }

        if (is_int($value)) {
            self::$id = Uuid::uuid4()->getBytes();

            return self::$id;
        }
        self::$id = Uuid::fromBytes((string)$value)->toString();
        return self::$id;

    }
}
