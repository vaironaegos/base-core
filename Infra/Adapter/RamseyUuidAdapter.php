<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Adapter;

use Ramsey\Uuid\Uuid;
use Astrotech\Core\Base\Adapter\Contracts\UuidTools;

final class RamseyUuidAdapter implements UuidTools
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
