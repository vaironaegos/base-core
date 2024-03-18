<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts;

use Astrotech\Core\Base\Exception\ValidationException;

interface ValidatorInterface
{
    /**
     * @throws ValidationException
     */
    public static function validate(string $field, mixed $value, string $validationRule): void;

    /**
     * @throws ValidationException
     */
    public static function validateBatch(array $values, array $validationRules): void;
}
