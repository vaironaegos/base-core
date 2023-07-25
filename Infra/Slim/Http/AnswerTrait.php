<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Slim\Http;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * Trait AnswerTrait
 * JSEND
 *
 * @link https://labs.omniti.com/labs/jsend
 * |------------------------------------------------------------------------------------------------------------------|
 * | Type     | Description                                             | Required Keys          | Optional Keys      |
 * |------------------------------------------------------------------------------------------------------------------|
 * | success  | All went well, and (usually) some data was returned.    | status, data           |                    |
 * |..........|.........................................................|........................|....................|
 * | fail     | There was a problem with the data submitted, or some    | status, data           |                    |
 * |          | pre-condition of the API call wasn't satisfied          |                        |                    |
 * |..........|.........................................................|........................|....................|
 * | error    | An error occurred in processing the request, i.e. an    | status, message        | code, data         |
 * |          | exception was thrown                                    |                        |                    |
 * |------------------------------------------------------------------------------------------------------------------|
 * @package Devitools\Http\Response\Answer
 */
trait AnswerTrait
{
    public function answerSuccess(mixed $data, array $meta = [], HttpStatus $code = HttpStatus::OK): Response
    {
        $newResponse = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($code->value);

        $newResponse->getBody()->write(json_encode([
            'status' => 'success',
            'data' => $data,
            'meta' => $meta
        ]));

        return $newResponse;
    }

    public function answerFail($data, array $meta = [], HttpStatus $code = HttpStatus::BAD_REQUEST)
    {
        $newResponse = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($code->value);

        $newResponse->getBody()->write(json_encode([
            'status' => 'fail',
            'data' => $data,
            'meta' => $meta
        ]));

        return $newResponse;
    }

    public function answerError($message, HttpStatus $code = HttpStatus::INTERNAL_SERVER_ERROR, $data = null)
    {
        $newResponse = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($code->value);

        $newResponse->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message,
            'meta' => $data
        ]));

        return $newResponse;
    }
}
