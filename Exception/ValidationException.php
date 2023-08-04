<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Exception;

use Exception;

class ValidationException extends ExceptionBase
{
    protected int|string $errorCode = 'VALIDATION_ERROR';
    protected int $statusCode = 400;

    public function __construct(array $details, string $message = '', ?int $code = 400, Exception $previous = null)
    {
        $this->statusCode = $code;
        parent::__construct($details, $message, $code, $previous);
    }

    public function getName(): string
    {
        return 'Validation Error';
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
