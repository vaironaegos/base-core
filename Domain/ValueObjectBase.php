<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain;

use ReflectionClass;
use JsonSerializable;
use Astrotech\ApiBase\Domain\Contracts\ValueObject;

abstract class ValueObjectBase implements ValueObject, JsonSerializable
{
    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return (string)$this->value();
    }

    /**
     * @inheritDoc
     */
    public function isEqualsTo(ValueObject $valueObject): bool
    {
        return $this->objectHash() === $valueObject->objectHash();
    }

    /**
     * @inheritDoc
     */
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
