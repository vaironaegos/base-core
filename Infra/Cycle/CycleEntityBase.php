<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Cycle;

use Astrotech\ApiBase\Domain\Contracts\ValueObject;
use Astrotech\ApiBase\Infra\Slim\Http\ControllerBase;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\Database\Schema\Attribute\ColumnAttribute;
use Cycle\ORM\RelationMap;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use ReflectionUnionType;

#[Index(columns: ['id'], unique: true)]
abstract class CycleEntityBase
{
    #[Column(
        type: 'varbinary(16)',
        name: 'id',
        primary: true,
        nullable: false
    )]
    protected string $id;

    public function __construct(?array $data)
    {
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
            Uuid::fromString($data['id'])->getBytes() :
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
        return $this->$name;
    }

    public function toArray(bool $toSnakeCase = false): array
    {
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

            if ($value instanceof CycleEntityBase) {
                if (isset($propertyList[$prop.'_id'])) {
                    unset($propertyList[$prop.'_id']);
                }
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

            if ($toSnakeCase) {
                $name = camelCaseToUnderscores($name);
            }

            $props[$name] = $value;
        }

        $props['id'] = $this->getId();

        return $props;
    }

    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return Uuid::fromBytes($this->id)->toString();
    }

    public function prePersist(): void
    {
        if (property_exists($this, 'createdAt') && empty($this->createdAt)) {
            $now = new DateTime();
            $this->createdAt = $now;
        }

        $loggedUser = ControllerBase::loggedUser();
        if (property_exists($this, 'createdBy')) {
            if (property_exists($this, 'firstName')) {
                $this->createdBy = $this->firstName . " [{$this->getId()}]";
                return;
            }

            $this->createdBy = $loggedUser['firstName'] . " [{$loggedUser['id']}]";
        }
    }

//    public function prepare(): self
//    {
//        $propertyList = get_object_vars($this);
//
//        foreach ($propertyList as $key => $value) {
//            if ($value instanceof CycleEntityBase) {
//                if (isUuidString($value->id)) {
//                    $value->id = Uuid::fromString($value->id)->getBytes();
//                }
//
//                $value->prepare();
//            }
//        }
//
//        return $this;
//    }
}
