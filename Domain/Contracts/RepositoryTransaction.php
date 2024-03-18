<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Contracts;

interface RepositoryTransaction
{
    public function beginTransaction(): self;
    public function commit(): void;
    public function rollback(): void;
}
