<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Exception;

use Astrotech\Core\Base\Exception\RuntimeException;

final class ImmutableDtoException extends RuntimeException
{
    public function __construct(string $paramName)
    {
        parent::__construct(sprintf(
            "Failed to set '%s' property in DTO '%s' class. DTO's are immutable",
            $paramName,
            get_called_class()
        ));
    }

    public function getName(): string
    {
        return 'Immutable DTO Error';
    }
}
