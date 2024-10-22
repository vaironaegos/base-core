<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts;

use Astrotech\Core\Base\Domain\UseCase\UseCaseOutput;

interface Command
{
    public static function handle(...$args): UseCaseOutput;
    public function handleClassName(): string;
}
