<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Exception;

use Astrotech\ApiBase\Exception\RuntimeException;

final class InvalidDtoParamException extends RuntimeException
{
    public function __construct(string $paramName)
    {
        parent::__construct(sprintf(
            "DTO '%s' property doesn't exist in '%s' class",
            $paramName,
            get_called_class()
        ));
    }

    public function getName(): string
    {
        return 'Invalid QueueSystem Param Error';
    }
}
