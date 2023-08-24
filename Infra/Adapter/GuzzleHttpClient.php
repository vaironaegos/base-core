<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Adapter;

use GuzzleHttp\Client;
use Astrotech\ApiBase\Adapter\Contracts\HttpClient;
use Psr\Http\Message\ResponseInterface as Response;

final class GuzzleHttpClient implements HttpClient
{
    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'base_uri' => '',
            'timeout' => 10
        ]);
    }

    public function get(string $uri, array $params = []): Response
    {
        return $this->httpClient->get($uri, [
            'debug' => $params['debug'] ?? false,
            'query' => $params['query'] ?? null,
            'headers' => $params['headers'] ?? []
        ]);
    }

    public function post(string $uri, array $params = []): Response
    {
        $requestParams = [
            'json' => $params['body'] ?? [],
            'debug' => $params['debug'] ?? false,
            'headers' => $params['headers'] ?? [],
            'query' => $params['query'] ?? null
        ];

        if (
            isset($requestParams['headers']['Content-Type'])
            && $requestParams['headers']['Content-Type'] === 'application/x-www-form-urlencoded'
        ) {
            $requestParams['form_params'] = $requestParams['json'];
            unset($requestParams['json']);
        }

        return $this->httpClient->post($uri, $requestParams);
    }

    public function put(string $uri, array $params = []): Response
    {
        $requestParams = [
            'json' => $params['body'] ?? [],
            'debug' => $params['debug'] ?? false,
            'headers' => $params['headers'] ?? [],
            'query' => $params['query'] ?? null
        ];

        if (
            isset($requestParams['headers']['Content-Type'])
            && $requestParams['headers']['Content-Type'] === 'application/x-www-form-urlencoded'
        ) {
            $requestParams['form_params'] = $requestParams['json'];
            unset($requestParams['json']);
        }

        return $this->httpClient->put($uri, $requestParams);
    }
}
