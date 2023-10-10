<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Cycle;

use Astrotech\ApiBase\Domain\Contracts\ValueObject;
use Astrotech\ApiBase\Infra\Slim\Http\ControllerBase;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\ORM\Entity\Behavior;
use Cycle\ORM\Entity\Behavior\Attribute\Listen;
use Cycle\ORM\Entity\Behavior\Event\Mapper\Command\OnCreate;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use ReflectionProperty;
use ReflectionUnionType;

#[Index(columns: ['id'], unique: true)]
abstract class CycleEntityBase
{
    #[Column(
        type: 'varbinary(16)',
        name: 'id',
        nullable: false,
        primary: true
    )]
    protected string $id;

    public function __construct(?array $data)
    {
        if (!$data) {
            return;
        }

        foreach ($data as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if (is_string($value) && isUuidString($value)) {
                $data[$key] = Uuid::fromString($value)->getBytes();
                continue;
            }

            if (is_string($value) && isDateUs($value)) {
                $data[$key] = new DateTimeImmutable($value . ' 00:00:00');
                continue;
            }

            if (is_string($value) && isDateTimeUs($value)) {
                $data[$key] = new DateTimeImmutable($value);
            }
        }

        $this->id = !empty($data['id']) && empty($this->id) ?
            $data['id'] :
            Uuid::uuid4()->getBytes();

        $this->fill($data);
    }

    public function fill(array $data): void
    {
        foreach ($data as $field => $value) {
            if ($field === 'id') {
                continue;
            }

            if (!property_exists($this, $field)) {
                continue;
            }

            // Logic to convert enums to string
            $reflectObject = new ReflectionClass($this);
            $reflectProperty = $reflectObject->getProperty($field);
            $isUnitType = $reflectProperty->getType() instanceof ReflectionUnionType;

            if ($isUnitType) {
                $this->$field = $value;
            }

            $propertyType = $isUnitType ? gettype($this->$field) : $reflectProperty->getType()->getName();

            if (!empty($value) && is_array($value) && !$isUnitType) {
                $propertyType = $reflectProperty->getType()->getName();
                if (is_a($propertyType, CycleEntityBase::class, true)) {
                    $value = new $propertyType($value);
                }
            }

            if (enum_exists($propertyType)) {
                $value = (!empty($value) ? $propertyType::tryFrom($value) : null);
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            $this->$field = $value;
        }
    }

    public function __set(string $name, mixed $value): void
    {
        $setter = 'set' . str_replace('_', '', ucwords($name, '_'));

        if ($name !== 'id' && method_exists($this, $setter)) {
            $this->{$setter}($value);
            return;
        }

        $this->$name = $value;
    }

    public function __get(string $name)
    {
        if (str_contains($name, '_')) {
            [$a, $attribute] = explode('_', $name);

            return $this->$attribute ?? null;
        }

        return $this->$name;
    }

    public function get(string $name)
    {
        return $this->$name;
    }


    public function toArray(bool $toSnakeCase = false, ?int $limit = 1, int $index = 0): array
    {
        $index++;

        if (!is_null($limit) && $index > $limit) {
            return [];
        }

        $props = [];
        $propertyList = get_object_vars($this);

        /** @var int|string|object $value */
        foreach ($propertyList as $prop => $value) {
            if (str_contains($prop, '__cycle')) {
                continue;
            }

            if ($value instanceof DateTimeInterface) {
                //$propertyList[$prop] = $value->format(DATE_ATOM);
                $propertyList[$prop] = $value->format('Y-m-d H:i:s');
                continue;
            }

            if ($value instanceof ValueObject) {
                $propertyList[$prop] = $value->value();
                continue;
            }

            if (is_array($value) && isset($value[0]) && $value[0] instanceof CycleEntityBase) {
                $newValues = [];

                foreach ($value as $newValue) {
                    if (isset($propertyList[$prop . '_id'])) {
                        unset($propertyList[$prop . '_id']);
                    }

                    $newValues[] = $newValue->toArray();
                }

                $propertyList[$prop] = $newValues;
                continue;
            }

            if ($value instanceof CycleEntityBase) {
                if (isset($propertyList[$prop . '_id'])) {
                    unset($propertyList[$prop . '_id']);
                }

                $propertyList[$prop] = $value->toArray();
                continue;
            }

            if (is_object($value) && enum_exists($value::class)) {
                $propertyList[$prop] = (!empty($value) ? $value->value : null);
                continue;
            }

            if (is_string($value)) {
                $propertyList[$prop] = trim($value);
                continue;
            }

            if (is_object($value)) {
                $reflectObject = new ReflectionClass(get_class($value));
                $properties = $reflectObject->getProperties();
                //                $propertyList[$prop] = [];

                foreach ($properties as $property) {
                    $property->setAccessible(true);
                    $propertyList[$prop][$property->getName()] = !$property->isInitialized($value) &&
                    $property->getType()->allowsNull() ? null : $property->getValue($value);
                }
            }
        }

        foreach ($propertyList as $name => $value) {
            if (str_contains($name, '__cycle')) {
                continue;
            }

            if (str_contains($name, '_id')) {
                continue;
            }

            if ($toSnakeCase) {
                $name = camelCaseToUnderscores($name);
            }

            $props[$name] = $value;
        }

        $props['id'] = $this->getId();

        return $props;
    }

    public function toViewArray(): array
    {
        return [
            'id'
        ];
    }

    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        if (isUuidString($this->id)) {
            return $this->id;
        }
        return Uuid::fromBytes($this->id)->toString();
    }

    public function prepare()
    {
        if (property_exists($this, 'createdAt') && empty($this->createdAt)) {
            $now = new DateTime();
            $this->createdAt = $now;
        }

        $loggedUser = ControllerBase::loggedUser();
        if (property_exists($this, 'createdBy')) {
            $this->createdBy = $loggedUser ?
                $loggedUser['firstName'] . " [{$loggedUser['id']}]" :
                $this->firstName . " [{$this->getId()}]";

//            if (!$loggedUser && property_exists($this, 'firstName')) {
//                $this->createdBy = $this->firstName . " [{$this->getId()}]";
//                return $this;
//            }
        }

        $attributes = clone $this;

        $propertyList = get_object_vars($attributes);

        foreach ($propertyList as $key => $value) {
            if ($key === 'id') {
                continue;
            }

            if ($value instanceof CycleEntityBase) {
                if (isUuidString($value->id)) {
                    $value->id = Uuid::fromString($value->id)->getBytes();
                }

                $attributes->{$key . '_id'} = $value->id;
                unset($attributes->$key);
                continue;
            }

            if (is_array($value)) {
                $reflectionProp = new ReflectionProperty($this, $key);

                $anotacoes = $reflectionProp->getDocComment();
            }
        }

        return $attributes;
    }
}
