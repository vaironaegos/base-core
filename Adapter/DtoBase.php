<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter;

use DateTimeInterface;
use ReflectionClass;
use JsonSerializable;
use ReflectionUnionType;
use Astrotech\Core\Base\Adapter\Contracts\Dto;
use Astrotech\Core\Base\Adapter\Exception\ImmutableDtoException;
use Astrotech\Core\Base\Adapter\Exception\InvalidDtoParamException;

abstract class DtoBase implements Dto, JsonSerializable
{
    public function values(): array
    {
        $values = get_object_vars($this);
        array_walk($values, fn (&$value, $property) => $value = $this->get($property));
        return $values;
    }

    public function get(string $property): mixed
    {
        $getter = "get" . ucfirst($property);

        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        if (!property_exists($this, $property)) {
            throw new InvalidDtoParamException($property);
        }

        $reflectObject = new ReflectionClass(get_called_class());
        $reflectProperty = $reflectObject->getProperty($property);
        $isUnitType = $reflectProperty->getType() instanceof ReflectionUnionType;

        if (!empty($value) && is_array($value) && !$isUnitType) {
            $propertyType = $reflectProperty->getType()->getName();
            if (is_a($propertyType, Dto::class, true)) {
                return new static(...$value);
            }
        }

        return $this->{$property};
    }

    /**
     * Converts the DTO to an associative array for JSON serialization.
     * @return array An associative array containing the DTO values.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return array_map(function (mixed $value) {
            if ($value instanceof DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }

            if ($value instanceof Dto) {
                $value = $value->toArray();
            }

            return $value;
        }, $this->values());
    }

    /**
     * Gets the value of a DTO attribute using property notation.
     * @param string $name The name of the attribute.
     * @return mixed The value of the attribute.
     * @throws InvalidDtoParamException
     */
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    /**
     * Prevents modification of DTO attributes (immutability).
     * @param string $name The name of the attribute.
     * @param mixed $value The value of the attribute.
     * @throws ImmutableDtoException Always throws an exception.
     */
    public function __set(string $name, mixed $value)
    {
        throw new ImmutableDtoException($name);
    }

    /**
     * Checks if a DTO attribute exists.
     * @param mixed $name The name of the attribute.
     * @return bool True if the attribute exists, false otherwise.
     */
    public function __isset(string $name): bool
    {
        return property_exists($this, $name);
    }
}
