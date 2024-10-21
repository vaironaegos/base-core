<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\UseCase;

use InvalidArgumentException;
use ReflectionClass;

class UseCaseOutputCode
{
    public const OK = 'ok';
    public const APPLICATION_ERROR = 'applicationError';
    public const RECORD_ALREADY_EXISTS = 'recordAlreadyExists';
    public const RECORD_NOT_FOUND = 'recordNotFound';
    public const RECORD_ALREADY_IN_USE = 'recordAlreadyInUse';
    public const FIELD_IS_INVALID = 'fieldIsInvalid';
    public const INVALID_SIGNATURE = 'invalidSignature';
    public const TOKEN_IS_EXPIRED = 'tokenIsExpired';
    public const TOKEN_IS_INVALID = 'tokenIsInvalid';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function create(string $value): self
    {
        $validValues = (new ReflectionClass(get_called_class()))->getConstants();

        if (!in_array($value, $validValues, true)) {
            throw new InvalidArgumentException("Invalid UseCaseOutputCode '$value'");
        }

        return new self($value);
    }

    public static function ok(): self
    {
        return self::create(static::OK);
    }

    public static function recordNotFound(): self
    {
        return self::create(static::RECORD_NOT_FOUND);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
