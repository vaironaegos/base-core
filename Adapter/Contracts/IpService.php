<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts;

interface IpService
{
    public function getIpDetails(string $ip): array;
}
