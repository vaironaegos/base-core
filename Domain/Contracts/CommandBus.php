<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Contracts;

use Astrotech\Core\Base\Adapter\Contracts\Dto;

interface CommandBus
{
    public function dispatch(Dto $command);
}
