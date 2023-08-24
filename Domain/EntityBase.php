<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain;

use UnitEnum;
use ReflectionClass;
use JsonSerializable;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionUnionType;
use Astrotech\ApiBase\Domain\Contracts\Entity;
use Astrotech\ApiBase\Domain\Contracts\ValueObject;
use Astrotech\ApiBase\Domain\Exceptions\EntityException;

/**
 * Class Entity
 * @package Astrotech\Shared\Domain
 *
 * @property int|string $id
 */
abstract class EntityBase implements Entity, JsonSerializable
{
    protected string|int $id = '';

    /**
     * Entity constructor.
     * @param array $values
     */
    final public function __construct(array $values)
    {
        foreach ($values as $property => $value) {
            $values[underscoreToCamelCase($property)] = $value;
            if (str_contains($property, '_')) {
                unset($values[$property]);
            }
        }
        $this->beforeInstantiating($values);
        $this->fill($values);
        $this->afterInstantiating();
    }

    protected function beforeInstantiating(array &$values): void
    {
    }

    protected function afterInstantiating(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function setId(string $id): void
    {
        $this->set('id', $id);
    }

    /**
     * @inheritDoc
     */
    public function getId(): string|int
    {
        return $this->get('id');
    }

    /**
     * @inheritDoc
     */
    public function idIsFilled(): bool
    {
        return !empty($this->getId());
    }

    /**
     * @inheritDoc
     */
    public function fill(array $values): void
    {
        foreach ($values as $attribute => $value) {
            $this->set($attribute, $value);
        }
    }

    /**
     * @inheritDoc
     */
    public function set(string $property, mixed $value): Entity
    {
        if (mb_strstr($property, '_') !== false) {
            $property = underscoreToCamelCase($property);
        }

        $setter = 'set' . str_replace('_', '', ucwords($property, '_'));

        if ($property !== 'id' && method_exists($this, $setter)) {
            $this->{$setter}($value);
            return $this;
        }

        if (!property_exists($this, $property)) {
            return $this;
        }

        $reflectObject = new ReflectionClass($this);
        $reflectProperty = $reflectObject->getProperty($property);

        if ($reflectProperty->isPrivate()) {
            return $this;
        }

        $isUnitType = $reflectProperty->getType() instanceof ReflectionUnionType;

        if (!$isUnitType) {
            $propertyType = $reflectProperty->getType()->getName();

            // Logic to convert entities properties to Entity class from array
            if (!empty($value) && is_array($value) && is_a($propertyType, Entity::class, true)) {
                $value = new $propertyType($value);
            }

            // Logic to convert value objects properties to ValueObject class by string
            if (is_string($value) && is_a($propertyType, ValueObject::class, true)) {
                $value = (!empty($value) ? new $propertyType($value) : null);
            }

            // Logic to convert enums to string
            if (is_int($value) && enum_exists($propertyType)) {
                $value = (!empty($value) ? $propertyType::tryFrom($value) : null);
            }

            // Logic to force convert boolean values
            if ($reflectProperty->getType()->getName() === 'bool') {
                $value = (bool) $value;
            }

            // Logic to force convert float values
            if ($reflectProperty->getType()->getName() === 'float') {
                $value = (float) $value;
            }
        }

        $isDateValue = (is_string($value) && (isDateUs($value) || isDateTimeUs($value) || isDateTimeIso($value)));

        if ($isDateValue) {
            $value = new DateTimeImmutable($value);
        }

        $this->{$property} = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get(string $property)
    {
        $getter = "get" . ucfirst($property);

        if ($property !== 'id' && method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        $className = get_called_class();

        if (!property_exists($this, $property)) {
            throw EntityException::propertyDoesNotExists($className, $property, [
                'className' => $className,
                'propertyName' => $property
            ]);
        }

        return $this->{$property};
    }

    public function toArray(bool $toSnakeCase = false): array
    {
        $props = [];
        $propertyList = get_object_vars($this);

        /** @var int|string|object $value */
        foreach ($propertyList as $prop => $value) {
            if ($value instanceof DateTimeInterface) {
                //$propertyList[$prop] = $value->format(DATE_ATOM);
                $propertyList[$prop] = $value->format('Y-m-d H:i:s');
                continue;
            }

            if ($value instanceof ValueObject) {
                $propertyList[$prop] = $value->value();
                continue;
            }

            if ($value instanceof Entity) {
                $propertyList[$prop] = $value->toArray();
                continue;
            }

            if (is_object($value) && enum_exists($value::class)) {
                $propertyList[$prop] = !empty($value) ? $value->value : null;
                continue;
            }

            if (is_object($value)) {
                $reflectObject = new ReflectionClass(get_class($value));
                $properties = $reflectObject->getProperties();
                //                $propertyList[$prop] = [];

                foreach ($properties as $property) {
                    $property->setAccessible(true);
                    $propertyList[$prop][$property->getName()] = $property->getValue($value);
                }
            }
        }

        foreach ($propertyList as $name => $value) {
            if ($toSnakeCase) {
                $name = camelCaseToUnderscores($name);
            }

            $props[$name] = $value;
        }

        return $props;
    }

    public function prepare(bool $toSnakeCase = false): array
    {
        $props = [];
        $propertyList = get_object_vars($this);

        /** @var int|string|object $value */
        foreach ($propertyList as $prop => $value) {
            $prop = $toSnakeCase ? camelCaseToUnderscores($prop) : $prop;
            $props[$prop] = $value;

            if ($value instanceof DateTimeInterface) {
                $props[$prop] = $value->format('Y-m-d H:i:s');
                continue;
            }

            if ($value instanceof ValueObject) {
                $props[$prop] = $value->value();
                continue;
            }

            if ($value instanceof Entity) {
                unset($props[$prop]);
                continue;
            }

            if ($value instanceof UnitEnum) {
                $props[$prop] = !empty($value->value) ? $value->value : null;
            }
        }

        return $props;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value)
    {
        $this->set($name, $value);
    }
}
