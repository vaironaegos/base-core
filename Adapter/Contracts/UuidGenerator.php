<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Contracts;

interface UuidGenerator
{
    public function create(): string;
    public function toBytes(string $uuid): string;
    public function toUuid(string $binary): string;
}
