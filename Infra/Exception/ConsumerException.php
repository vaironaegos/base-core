<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Exception;

use Exception;
use Astrotech\Core\Base\Exception\ExceptionBase;

class ConsumerException extends ExceptionBase
{
    protected int | string $errorCode = 'CONSUMER_ERROR';

    public function __construct(string $message = '', array $details = [], ?int $code = 500, Exception $previous = null)
    {
        ExceptionBase::__construct($details, $message, $code, $previous);
    }

    public function getName(): string
    {
        return 'Consumer Error';
    }
}
