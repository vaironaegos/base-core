<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts;

use Psr\Http\Message\ResponseInterface as Response;

interface HttpClient
{
    /**
     * Send a GET request.
     * @param string $uri The URI to send the GET request to.
     * @param array $params Optional parameters to include in the GET request.
     * @return Response
     */
    public function get(string $uri, array $params = []): Response;

    /**
     * Send a POST request.
     * @param string $uri The URI to send the POST request to.
     * @param array $body The body of the POST request, if applicable.
     * @param array $params Optional parameters to include in the POST request.
     * @return Response
     */
    public function post(string $uri, array $body = [], array $params = []): Response;

    /**
     * Send a PUT request.
     * @param string $uri The URI to send the PUT request to.
     * @param array $body The body of the PUT request, if applicable.
     * @param array $params Optional parameters to include in the PUT request.
     * @return Response
     */
    public function put(string $uri, array $body = [], array $params = []): Response;

    /**
     * Send a PATCH request.
     * @param string $uri The URI to send the PATCH request to.
     * @param array $body The body of the PATCH request, if applicable.
     * @param array $params Optional parameters to include in the PATCH request.
     * @return Response
     */
    public function patch(string $uri, array $body = [], array $params = []): Response;

    /**
     * Send a DELETE request.
     * @param string $uri The URI to send the DELETE request to.
     * @param array $params Optional parameters to include in the DELETE request.
     * @return Response
     */
    public function delete(string $uri, array $params = []): Response;
}
