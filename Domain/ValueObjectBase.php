<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain;

use ReflectionClass;
use JsonSerializable;
use Astrotech\Core\Base\Domain\Contracts\ValueObject;

abstract class ValueObjectBase implements ValueObject, JsonSerializable
{
    public function __toString(): string
    {
        return (string)$this->value();
    }

    public function isEqualsTo(ValueObject $valueObject): bool
    {
        return $this->objectHash() === $valueObject->objectHash();
    }

    public function objectHash(): string
    {
        $reflectObject = new ReflectionClass(get_class($this));
        $props = $reflectObject->getProperties();
        $value = '';

        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $value .= $prop->getValue($this);
        }

        return md5($value);
    }

    public function jsonSerialize(): mixed
    {
        return $this->value();
    }

    public function createFrom(mixed $value): ValueObject
    {
        return new static($value);
    }
}
