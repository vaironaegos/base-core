<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Contracts;

interface LogSystem
{
    public function debug(string $message, array $options = []): void;
}
