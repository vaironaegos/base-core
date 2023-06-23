<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter;

use Astrotech\ApiBase\Adapter\Contracts\Dto;
use Astrotech\ApiBase\Adapter\Exception\ImmutableDtoException;
use Astrotech\ApiBase\Adapter\Exception\InvalidDtoParamException;
use JsonSerializable;
use ReflectionClass;
use ReflectionUnionType;

/**
 * Class DtoBase
 */
abstract class DtoBase implements Dto, JsonSerializable
{
    /**
     * {@inheritdoc}
     */
    public function values(): array
    {
        $values = get_object_vars($this);
        array_walk($values, fn (&$value, $property) => $value = $this->get($property));
        return $values;
    }

    public static function createFromArray(array $values): static
    {
        foreach ($values as $property => $value) {
            $reflectObject = new ReflectionClass(get_called_class());
            if ($reflectObject->hasProperty($property)) {
                $reflectProperty = $reflectObject->getProperty($property);

                $isUnitType = $reflectProperty->getType() instanceof ReflectionUnionType;

                if (!empty($value) && is_array($value) && !$isUnitType) {
                    $propertyType = $reflectProperty->getType()->getName();
                    if (is_a($propertyType, Dto::class, true)) {
                        $value = $propertyType::createFromArray($value);
                    }
                }
            }

            $values[underscoreToCamelCase($property)] = $value;

            if (mb_strstr($property, '_') === false) {
                continue;
            }
            unset($values[$property]);
        }

        return new static(...$values);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $property): mixed
    {
        $getter = "get" . ucfirst($property);

        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        if (!property_exists($this, $property)) {
            throw new InvalidDtoParamException($property);
        }

        return $this->{$property};
    }

    public function jsonSerialize(): mixed
    {
        return $this->values();
    }

    public function rules(): array
    {
        return [];
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value)
    {
        throw new ImmutableDtoException($name);
    }

    public function __isset($name): bool
    {
        return property_exists($this, $name);
    }
}
