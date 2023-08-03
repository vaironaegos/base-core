<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Adapter;

use Astrotech\ApiBase\Adapter\Contracts\UuidGenerator;
use Ramsey\Uuid\Uuid;

final class RamseyUuidAdapter implements UuidGenerator
{
    public function create(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function toBytes(string $uuid): string
    {
        return Uuid::fromString($uuid)->getBytes();
    }

    public function toUuid(string $binary): string
    {
        return Uuid::fromBytes($binary)->toString();
    }
}
