<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Exception;

use Astrotech\Core\Base\Exception\RuntimeException;

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
        return 'Invalid DTO Param Error';
    }
}
