<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\Contracts;

interface Repository
{
    public function create(Entity $entity): string|int;
    public function read(string|int $id, string $entityClassName): ?Entity;
    public function update(Entity $entity): bool;
    public function delete(string|int $id): bool;
}
