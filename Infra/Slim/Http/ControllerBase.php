<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Slim\Http;

use Error;
use Throwable;
use InvalidArgumentException;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\DBAL\Exception\DriverException;
use Astrotech\ApiBase\Exception\RuntimeException;
use Psr\Http\Message\ResponseInterface as Response;
use Astrotech\ApiBase\Exception\ValidationException;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class ControllerBase
{
    use AnswerTrait;

    protected Request $request;
    protected Response $response;
    protected array $args;
    protected array $meta = [];
    protected static array $loggedUser = [];

    abstract public function handle(Request $request): array;

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args = []): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        $this->parseBody();

        try {
            return $this->answerSuccess($this->handle($this->request), $this->meta);
        } catch (ValidationException $e) {
            $this->meta = [
                ...$this->meta,
                'error' => [
                    'name' => $e->getName(),
                    'code' => $e->getCode(),
                    'details' => $e->details(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'stackTrace' => $e->getTrace()
                ]
            ];

            return $this->answerFail($e->details(), $this->meta, HttpStatus::tryFrom($e->getStatusCode()));
        } catch (DriverException $e) {
            $this->meta = [
                ...$this->meta,
                'error' => [
                    'name' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'details' => [
                        'sql' => $e->getQuery()->getSQL(),
                        'values' => $e->getQuery()->getParams()
                    ],
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'stackTrace' => $e->getTrace()
                ]
            ];

            return $this->answerError(message: $e->getMessage(), data: $this->meta);
        } catch (RuntimeException $e) {
            $this->meta = [
                ...$this->meta,
                'error' => [
                    'name' => $e->getName(),
                    'code' => $e->getCode(),
                    'details' => [],
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'stackTrace' => $e->getTrace()
                ]
            ];

            return $this->answerError(message: $e->getMessage(), data: $this->meta);
        } catch (Throwable | Error | InvalidArgumentException | MappingException $e) {
            $this->meta = [
                ...$this->meta,
                'error' => [
                    'name' => $e::class,
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'details' => [],
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'stackTrace' => $e->getTrace()
                ]
            ];

            return $this->answerError(message: $e->getMessage(), data: $this->meta);
        }
    }

    protected function queryParam(string $name, string $default = null): mixed
    {
        $params = $this->request->getQueryParams();

        if (isset($params[$name]) && is_string($params[$name])) {
            return trim($params[$name]);
        }

        if (isset($params[$name]) && ctype_digit($params[$name])) {
            return floatval($params[$name]);
        }

        if (isset($params[$name]) && is_bool($params[$name])) {
            return convertToBool($params[$name]);
        }

        return $default;
    }

    protected function post(string $name = null, string $default = null): mixed
    {
        $parsedBody = $this->request->getParsedBody();

        if (isset($parsedBody[$name]) && is_string($parsedBody[$name])) {
            return trim($parsedBody[$name]);
        }

        if (isset($parsedBody[$name]) && is_bool($parsedBody[$name])) {
            return convertToBool($parsedBody[$name]);
        }

        if (isset($parsedBody[$name]) && ctype_digit((string)$parsedBody[$name])) {
            return $parsedBody[$name];
        }

        return $default;
    }

    protected function allPost(array $only = []): array
    {
        $parsedBody = array_keys($this->request->getParsedBody() ?? []);
        $data = [];

        foreach ($parsedBody as $name) {
            if (!empty($only) && !in_array($name, $only)) {
                continue;
            }

            $data[$name] = $this->post($name);
        }

        return $data;
    }

    protected function allQueryParams(): array
    {
        $params = array_keys($this->request->getQueryParams() ?? []);
        $data = [];

        foreach ($params as $name) {
            $data[$name] = $this->queryParam($name);
        }

        return $data;
    }

    private function parseBody(): void
    {
        $contentType = $this->request->getHeaderLine('Content-Type');

        if (!strstr($contentType, 'application/json')) {
            return;
        }

        $contents = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        $this->request->withParsedBody($contents);
    }

    protected function getLoggedUser(): array
    {
        $header = $this->request->getHeaderLine('X-SkyEx-User');

        if (!$header) {
            return [];
        }

        self::$loggedUser = json_decode(current($this->request->getHeader('X-SkyEx-User')), true);

        return self::$loggedUser;
    }

    public static function loggedUser(): array
    {
        return self::$loggedUser;
    }

    protected function getSettings(): array
    {
        $header = $this->request->getHeader('X-SkyEx-Settings');

        if (!$header) {
            return [];
        }

        return json_decode(current($this->request->getHeader('X-SkyEx-Settings')), true);
    }
}
