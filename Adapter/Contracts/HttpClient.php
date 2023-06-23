<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Contracts;

use Psr\Http\Message\ResponseInterface as Response;

interface HttpClient
{
    public function get(string $uri, array $params = []): Response;
    public function post(string $uri, array $params = []): Response;
    public function put(string $uri, array $params = []): Response;
}
