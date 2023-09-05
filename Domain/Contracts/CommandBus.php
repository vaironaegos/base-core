<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\Contracts;

use Astrotech\ApiBase\Adapter\Contracts\Dto;

interface CommandBus
{
    public function dispatch(Dto $command);
}
