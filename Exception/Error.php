<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Exception;

interface Error
{
    public function details(): array;
    public function getName(): string;
}
