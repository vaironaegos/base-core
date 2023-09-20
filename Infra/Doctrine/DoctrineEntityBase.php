<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Doctrine;

use DateTime;
use ReflectionClass;
use Ramsey\Uuid\Uuid;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionUnionType;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Astrotech\ApiBase\Domain\Contracts\ValueObject;
use Astrotech\ApiBase\Infra\Slim\Http\ControllerBase;

#[HasLifecycleCallbacks]
abstract class DoctrineEntityBase
{
    #[Id, Column(name: 'id', type: 'binary', length: 16, nullable: false)]
    protected mixed $id = '';

    public function __construct(?array $data)
    {
        foreach ($data as $key => $value) {
            if (empty($value)) {
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

        if (isset($this->id) && is_resource($this->id)) {
            fseek($this->id, 0);
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
                if (is_a($propertyType, DoctrineEntityBase::class, true)) {
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
            $this->id = Uuid::fromBytes(stream_get_contents($this->id))->toString();
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
                $propertyList[$prop] = (!empty($value) ? $value->value : null);
                continue;
            }

            if ($value instanceof Collection) {
                $propertyList[$prop] = $value->toArray();
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

    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    public function getBinaryId(): string
    {
        if (!is_resource($this->id)) {
            return $this->id;
        }
        fseek($this->id, 0);
        return stream_get_contents($this->id);
    }

    #[PrePersist]
    public function populateCreationBlameables(): void
    {
        if (is_resource($this->id)) {
            fseek($this->id, 0);
        }

        if (property_exists($this, 'createdAt') && empty($this->createdAt)) {
            $now = new DateTime();
            $this->createdAt = $now;
        }

        $loggedUser = ControllerBase::loggedUser();
        if ($loggedUser && property_exists($this, 'createdBy')) {
            $this->createdBy = $loggedUser['firstName'] . " [{$loggedUser['id']}]";
        }
    }

    public function prepare(): self
    {
        $propertyList = get_object_vars($this);

        foreach ($propertyList as $key => $value) {
            if ($value instanceof DoctrineEntityBase) {
                if (!is_resource($value->id)) {
                    continue;
                }

                fseek($value->id, 0);
                $value->id = stream_get_contents($value->id);
                $value->prepare();
            }
        }

        return $this;
    }

    //    #[PreUpdate]
    //    public function populateUpdateBlameables(): void
    //    {
    //@todo Verificar o motivo de quando ativa para preencher os campos updated by e at não está persistindo na base.
    //        if (property_exists($this, 'updatedAt')) {
    //            $this->updatedAt = new DateTime();
    //            if (property_exists($this, 'updatedBy')) {
    //                $this->updatedBy = $this->getFullName() ." [{$this->getId()}]";
    //            }
    //        }
    //    }
}
