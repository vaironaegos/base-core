<?php

namespace Astrotech\ApiBase\Infra\Slim\Http;

use Astrotech\ApiBase\Exception\RuntimeException;
use Astrotech\ApiBase\Exception\ValidationException;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\Mapping\MappingException;
use Error;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

abstract class ControllerBase
{
    use AnswerTrait;

    protected Request $request;
    protected Response $response;
    protected array $args;

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
            return $this->answerSuccess($this->handle($this->request));
        } catch (ValidationException $e) {
            $meta = [];

            if (!APP_IS_PRODUCTION) {
                $meta = [
                    'name' => $e->getName(),
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'stackTrace' => $e->getTrace()
                ];
            }

            return $this->answerFail($e->details(), $meta, HttpStatus::tryFrom($e->getStatusCode()));
        } catch (DriverException $e) {
            $meta = [];

            if (!APP_IS_PRODUCTION) {
                $meta = [
                    'name' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'query' => $e->getQuery()->getSQL(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'stackTrace' => $e->getTrace()
                ];
            }

            return $this->answerError(message: $e->getMessage(), data: $meta);
        } catch (RuntimeException $e) {
            $meta = [];

            if (!APP_IS_PRODUCTION) {
                $meta = [
                    'name' => $e->getName(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'stackTrace' => $e->getTrace()
                ];
            }

            return $this->answerError(message: $e->getMessage(), data: $meta);
        } catch (Throwable | Error | InvalidArgumentException | MappingException $e) {
            $meta = [];

            if (!APP_IS_PRODUCTION) {
                $meta = [
                    'name' => ($e instanceof Error) || ($e instanceof InvalidArgumentException) ? $e->getMessage()
                        : $e->getName(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'stackTrace' => $e->getTrace()
                ];
            }

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

    protected function getLoggedUser(): array
    {
        $header = $this->request->getHeader('X-SkyEx-User');

        if (!$header) {
            return [];
        }

        return json_decode(current($header), true)['data'];
    }
}
