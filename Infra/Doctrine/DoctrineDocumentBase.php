<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Doctrine;

use ReflectionClass;
use DateTimeInterface;
use Astrotech\ApiBase\Domain\Contracts\ValueObject;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

abstract class DoctrineDocumentBase
{
    /**
     * @ODM\Id(strategy="NONE")
     */
    protected string $id;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function fill(array $data): void
    {
        foreach ($data as $field => $value) {
            if (!property_exists($this, $field)) {
                continue;
            }

            if ($field === 'id') {
                $this->setId($field);
            }

            $this->$field = $value;
        }
    }

    public function toArray(bool $toSnakeCase = false): array
    {
        $props = [];
        $propertyList = get_object_vars($this);

        /** @var int|string|object $value */
        foreach ($propertyList as $prop => $value) {
            if ($value instanceof DateTimeInterface) {
                $propertyList[$prop] = $value->format('Y-m-d H:i:s');
                continue;
            }

            if ($value instanceof ValueObject) {
                $propertyList[$prop] = $value->value();
                continue;
            }

            if ($value instanceof DoctrineEntityBase) {
                $propertyList[$prop] = $value->toArray();
                continue;
            }

            if (is_object($value) && enum_exists($value::class)) {
                $propertyList[$prop] = (!empty($value) ? $value->value : null);
                continue;
            }

            if (is_object($value)) {
                $reflectObject = new ReflectionClass(get_class($value));
                $properties = $reflectObject->getProperties();

                foreach ($properties as $property) {
                    $property->setAccessible(true);
                    $propertyList[$prop][$property->getName()] = !$property->isInitialized($value) &&
                    $property->getType()->allowsNull() ? null : $property->getValue($value);
                }
            }
        }

        foreach ($propertyList as $name => $value) {
            if ($toSnakeCase) {
                $name = camelCaseToUnderscores($name);
            }

            $props[$name] = $value;
        }

        $props['id'] = $this->getId();

        return $props;
    }

    public function toSoftArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }

    public function __get(string $name): mixed
    {
        return $this->$name;
    }
}
