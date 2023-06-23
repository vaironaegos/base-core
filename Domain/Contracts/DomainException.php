<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\Contracts;

interface DomainException
{
    public function details(): array;
}
