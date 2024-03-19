<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Contracts;

interface Entity
{
    /**
     * Setter method for setting the identifier of the entity.
     * @param string|int $id The identifier to set for the entity.
     * @return void
     */
    public function setId(string|int $id): void;

    /**
     * Getter method for retrieving the identifier of the entity.
     * @return int|string The identifier of the entity.
     */
    public function getId(): string|int;

    /**
     * Method to check if the identifier of the entity has been set.
     * @return bool True if the identifier is set, false otherwise.
     */
    public function idIsFilled(): bool;

    /**
     * Method for populating an entity with data from an associative array.
     * @param array $values Associative array containing entity properties.
     * @return void
     */
    public function fill(array $values): void;

    /**
     * Method for setting a property of the entity.
     * @param string $property The name of the property to set.
     * @param mixed $value The value to set for the property.
     * @return Entity Returns the entity instance for method chaining.
     */
    public function set(string $property, mixed $value): Entity;

    /**
     * Method for getting the value of a property of the entity.
     * @param string $property The name of the property to retrieve.
     * @return mixed The value of the specified property.
     */
    public function get(string $property): mixed;

    /**
     * Outputs an array representation of the entity based on its properties.
     * @param bool $toSnakeCase Whether to convert property names to snake case.
     * @return array An array representation of the entity.
     */
    public function toArray(bool $toSnakeCase = false): array;

    /**
     * Method to prepare entity data for persistence mechanisms.
     * @return array An array containing entity data prepared for persistence.
     */
    public function prepare(): array;
}
