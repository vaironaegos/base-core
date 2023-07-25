<?php

namespace Astrotech\ApiBase\Infra\Doctrine;

use Astrotech\ApiBase\Domain\Contracts\ValueObject;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\PrePersist;
use Ramsey\Uuid\Uuid;
use ReflectionClass;

#[HasLifecycleCallbacks]
abstract class DoctrineEntityBase
{
    #[
        Id,
        Column(name: 'id', type: 'binary', length: 16, nullable: false)
    ]
    protected mixed $id = '';

    public function __construct(?array $data)
    {
        if (isset($this->id) && is_resource($this->id)) {
            fseek($this->id, 0);
        }

        $this->id = isset($data['id']) && !empty($data['id']) && !isset($this->id) ?
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
            $propertyType = $reflectProperty->getType()->getName();

            if (enum_exists($propertyType)) {
                $value = (!empty($value) ? $propertyType::tryFrom($value) : null);
            }

            $this->$field = $value;
        }
    }

    public function __get(string $name): mixed
    {
        return $this->$name;
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

    public function toArray(bool $toSnakeCase = false): array
    {
        if (is_resource($this->id)) {
            fseek($this->id, 0);
        }

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

            if ($value instanceof DoctrineEntityBase) {
                $propertyList[$prop] = $value->toArray();
                continue;
            }

            if (is_object($value) && enum_exists($value::class)) {
                $value = (!empty($value) ? $value->value : null);
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
            if ($toSnakeCase) {
                $name = camelCaseToUnderscores($name);
            }

            $props[$name] = $value;
        }

        $props['id'] = $this->getId();

        return $props;
    }

    public function getId(): string
    {
        if (isUuidString($this->id)) {
            return $this->id;
        }

        if (is_resource($this->id)) {
            fseek($this->id, 0);
            return Uuid::fromBytes(stream_get_contents($this->id))->toString();
        }

        return Uuid::fromBytes($this->id)->toString();
    }

    public function getBinaryId(): string
    {
        fseek($this->id, 0);
        return stream_get_contents($this->id);
    }

    #[PrePersist]
    public function populateCreationBlameables(): void
    {
        if (property_exists($this, 'createdAt')) {
            $now = new DateTime();
            $this->createdAt = $now;
            if (property_exists($this, 'createdBy')) {
                $this->createdBy = $this->getFullName() . " [{$this->getId()}]";
            }
        }
    }

//    #[PreUpdate]
//    public function populateUpdateBlameables(): void
//    {
////        @todo Verificar o motivo de quando ativa para preencher os campos updated by e at não está persistindo na base.
//        if (property_exists($this, 'updatedAt')) {
//            $this->updatedAt = new DateTime();
//            if (property_exists($this, 'updatedBy')) {
//                $this->updatedBy = $this->getFullName() ." [{$this->getId()}]";
//            }
//        }
//    }
}
