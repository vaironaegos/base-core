<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts;

interface LogSystem
{
    public function trace(string $message, array $options = []): void;
    public function debug(string $message, array $options = []): void;
    public function info(string $message, array $options = []): void;
    public function warning(string $message, array $options = []): void;
    public function error(string $message, array $options = []): void;
    public function fatal(string $message, array $options = []): void;
}
