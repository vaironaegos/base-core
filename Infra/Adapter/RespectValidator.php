<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Adapter;

use Respect\Validation\Validator;
use Astrotech\ApiBase\Exception\ValidationException;
use Astrotech\ApiBase\Adapter\Contracts\ValidatorInterface;

/**
 * For all Respect/Validation rules
 * @see https://respect-validation.readthedocs.io/en/latest/
 */
final class RespectValidator implements ValidatorInterface
{
    public static function validateBatch(array $value, array $validationRules): void
    {
        foreach ($validationRules as $field => $rules) {
            foreach ($rules as $rule2) {
                if (str_contains($rule2, 'enum:')) {
                    [$rule, $class] = explode(':', $rule2);
                    $isValid = $class::tryFrom($value[$field]);
                    if (!$isValid) {
                        throw new ValidationException(['field' => $field, 'error' => 'validation.' . $rule2]);
                    }
                    continue;
                }

                $isValid = Validator::{$rule2}()->validate($value[$field]);
                if (!$isValid) {
                    throw new ValidationException(['field' => $field, 'error' => 'validation.' . $rule2]);
                }
            }
        }
    }

    public static function validate(string $field, mixed $value, string $validationRules): void
    {
        $rules = explode('|', $validationRules);
        foreach ($rules as $rule2) {
            $isValid = Validator::{$rule2}()->validate($value);
            if (!$isValid) {
                throw new ValidationException(['field' => $field, 'error' => 'validation.' . $rule2]);
            }
        }
    }
}
