<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Adapter;

use Astrotech\Core\Base\Adapter\Contracts\HttpClient;
use Astrotech\Core\Base\Adapter\Contracts\IpService;

final class IpStackService implements IpService
{
    private string $baseUrl = 'https://api.ipstack.com';

    public function __construct(
        private readonly string $accessKey,
        private readonly HttpClient $httpClient
    ) {
    }

    public function getIpDetails(string $ip): array
    {
        $response = $this->httpClient->get("{$this->baseUrl}/{$ip}?access_key={$this->accessKey}");
        $details = $response->getBody()->getContents();

        return json_decode($details, true);
    }
}
