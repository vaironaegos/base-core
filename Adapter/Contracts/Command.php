<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts;

interface Command
{
    public static function handle(...$args): mixed;
    public function handleClassName(): string;
}
