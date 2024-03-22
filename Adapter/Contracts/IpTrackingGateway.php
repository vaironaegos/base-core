<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts;

interface IpTrackingGateway
{
    public function getIpDetails(string $ip): array;
}
