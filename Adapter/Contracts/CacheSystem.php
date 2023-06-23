<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Contracts;

interface CacheSystem
{
    public function has(string $key): bool;
    public function get(string $key): mixed;
    public function set(string $key, mixed $value): void;
}
