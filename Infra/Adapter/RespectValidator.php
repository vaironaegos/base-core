<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Adapter;

use Respect\Validation\Validator;
use Astrotech\Core\Base\Exception\ValidationException;
use Astrotech\Core\Base\Adapter\Contracts\ValidatorInterface;

/**
 * For all Respect/Validation rules
 * @see https://respect-validation.readthedocs.io/en/latest/
 */
final class RespectValidator implements ValidatorInterface
{
    public static function validate(string $field, mixed $value, string $validationRule): void
    {
        $rules = explode('|', $validationRule);
        foreach ($rules as $rule2) {
            if (str_contains($rule2, 'enum:')) {
                $rule2 = str_replace('enum:', '', $rule2);
                if ($rule2::tryFrom($value)) {
                    continue;
                }
                throw new ValidationException(['field' => $field, 'error' => 'validation.enum', 'value' => $value]);
            }

            if (!Validator::{$rule2}()->validate($value)) {
                throw new ValidationException(['field' => $field, 'error' => "validation.{$rule2}", 'value' => $value]);
            }
        }
    }

    public static function validateBatch(array $values, array $validationRules): void
    {
        foreach ($validationRules as $fieldName => $ruleList) {
            if (!isset($values[$fieldName])) {
                throw new ValidationException(['field' => $fieldName, 'error' => 'validation.fieldNotExists']);
            }
            static::validate($fieldName, $values[$fieldName], implode('|', $ruleList));
        }
    }
}
