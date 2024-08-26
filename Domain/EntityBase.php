<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain;

use UnitEnum;
use ReflectionClass;
use JsonSerializable;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionUnionType;
use Astrotech\Core\Base\Domain\Contracts\Entity;
use Astrotech\Core\Base\Domain\Contracts\ValueObject;
use Astrotech\Core\Base\Domain\Collection\CollectionBase;
use Astrotech\Core\Base\Domain\Exceptions\EntityException;

abstract class EntityBase implements Entity, JsonSerializable
{
    protected string|int $id = '';
    private bool $isConstructor = false;

    final public function __construct(array $values)
    {
        $this->isConstructor = true;

        foreach ($values as $property => $value) {
            $values[underscoreToCamelCase($property)] = $value;
            if (str_contains($property, '_')) {
                unset($values[$property]);
            }
        }
        $this->beforeInstantiating($values);
        $this->fill($values);
        $this->afterInstantiating();
        $this->isConstructor = false;
    }

    /**
     * Hook method called before entity instantiation.
     * @param array $values The array of values passed to the constructor.
     * @return void
     */
    protected function beforeInstantiating(array &$values): void
    {
    }

    /**
     * Hook method called after entity instantiation.
     * @return void
     */
    protected function afterInstantiating(): void
    {
    }

    public function setId(string|int $id): void
    {
        $this->set('id', $id);
    }

    public function getId(): string|int
    {
        return $this->get('id');
    }

    public function idIsFilled(): bool
    {
        return !empty($this->getId());
    }

    public function fill(array $values): void
    {
        foreach ($values as $attribute => $value) {
            $this->set($attribute, $value);
        }
    }

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

        if (property_exists($this, 'isConstructor') && !$this->isConstructor && $reflectProperty->isPrivate()) {
            return $this;
        }

        $isUnitType = $reflectProperty->getType() instanceof ReflectionUnionType;

        if ($isUnitType) {
            $this->$property = $value;
        }

        $propertyType = $isUnitType ? gettype($this->$property) : $reflectProperty->getType()->getName();

        // Logic to convert entities properties to Entity class from array
        if (!empty($value) && is_array($value) && is_a($propertyType, Entity::class, true)) {
            $value = new $propertyType($value);
        }

        // Logic to convert value objects properties to ValueObject class by string
        if (is_string($value) && is_a($propertyType, ValueObject::class, true)) {
            $value = (!empty($value) ? new $propertyType($value) : null);
        }

        // Logic to fill CollectionBase classes from array
        if (is_array($value) && is_a($propertyType, CollectionBase::class, true)) {
            $value = new $propertyType($value);
        }

        // Logic to convert enums to string
        if (enum_exists($propertyType) && is_string($value) || enum_exists($propertyType) && is_int($value)) {
            $value = (!empty($value) ? $propertyType::tryFrom($value) : null);
        }

        // Logic to force convert boolean values
        if ($propertyType === 'bool') {
            $value = (bool)$value;
        }

        // Logic to force convert float values
        if ($propertyType === 'float') {
            $value = (float)$value;
        }

        $isDateValue = (
            is_string($value) &&
            (isDateIso8601($value) || isDateTimeIso8601($value) || isDateTimeIso($value)) ||
            $propertyType instanceof DateTimeInterface && !is_null($value) ||
            $propertyType == 'DateTimeInterface' && !is_null($value)
        );

        if ($isDateValue) {
            $value = new DateTimeImmutable($value);
        }

        //        $this->{$property} = $value;

        $reflectProperty->setValue($this, $value);
        return $this;
    }

    public function get(string $property): mixed
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
        unset($this->isConstructor);

        $props = [];
        $propertyList = [];

        $reflectObject = new ReflectionClass($this);
        $reflectProperties = $reflectObject->getProperties();

        foreach ($reflectProperties as $property) {
            $propertyList[$property->getName()] = $property->getValue($this);
        }

        /** @var int|string|object $value */
        foreach ($propertyList as $prop => $value) {
            if (empty($value) && $prop === 'id') {
                unset($propertyList[$prop]);
                continue;
            }

            if ($value instanceof DateTimeInterface) {
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

            if ($value instanceof CollectionBase) {
                $propertyList[$prop] = $value->getItems();
                continue;
            }

            if (is_object($value) && enum_exists($value::class)) {
                $propertyList[$prop] = $value->value;
                continue;
            }

            if (is_object($value)) {
                $reflectObject = new ReflectionClass(get_class($value));
                $properties = $reflectObject->getProperties();
                $propertyList[$prop] = [];

                foreach ($properties as $property) {
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
                //$props[$prop] = $value->prepare();
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
