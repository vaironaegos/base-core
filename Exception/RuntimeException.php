<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Exception;

use Exception;

final class RuntimeException extends ExceptionBase
{
    protected int | string $errorCode = 'RUNTIME_ERROR';

    public function __construct(string $message = '', array $details = [], ?int $code = 0, Exception $previous = null)
    {
        parent::__construct($details, $message, $code, $previous);
    }

    public function getName(): string
    {
        return 'Runtime Application Error';
    }
}
