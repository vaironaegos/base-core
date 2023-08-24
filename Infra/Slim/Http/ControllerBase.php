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
    protected static array $loggedUser = [];

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
            return $this->answerSuccess($this->handle($this->request));
        } catch (ValidationException $e) {
            $meta = [
                'error' => [
                    'name' => $e->getName(),
                    'code' => $e->getCode(),
                    'details' => $e->details(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'stackTrace' => $e->getTrace()
                ]
            ];

            return $this->answerFail($e->details(), $meta, HttpStatus::tryFrom($e->getStatusCode()));
        } catch (DriverException $e) {
            $meta = [
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

            return $this->answerError(message: $e->getMessage(), data: $meta);
        } catch (RuntimeException $e) {
            $meta = [
                'error' => [
                    'name' => $e->getName(),
                    'code' => $e->getCode(),
                    'details' => [],
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'stackTrace' => $e->getTrace()
                ]
            ];

            return $this->answerError(message: $e->getMessage(), data: $meta);
        } catch (Throwable | Error | InvalidArgumentException | MappingException $e) {
            $meta = [
                'error' => [
                    'name' => $e::class,
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'details' => [],
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'stackTrace' => $e->getTrace()
                ]
            ];

            return $this->answerError(message: $e->getMessage(), data: $meta);
        }
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

    abstract public function handle(Request $request): array;

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
