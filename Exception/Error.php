<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Exception;

interface Error
{
    public function details(): array;
    public function getName(): string;
}
