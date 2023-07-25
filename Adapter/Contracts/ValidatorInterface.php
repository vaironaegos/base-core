<?php

namespace Astrotech\ApiBase\Adapter\Contracts;

interface ValidatorInterface
{
    public static function validateBatch(array $value, array $validationRules): void;

    public static function validate(string $field, mixed $value, string $validationRules): void;
}
