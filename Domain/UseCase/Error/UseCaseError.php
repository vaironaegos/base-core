<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\UseCase\Error;

interface UseCaseError
{
    public function output(): array;
}
