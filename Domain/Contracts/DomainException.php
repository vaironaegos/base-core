<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Contracts;

interface DomainException
{
    public function details(): array;
}
