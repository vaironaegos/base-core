<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Contracts;

/**
 * Interface QueueSystem
 */
interface Dto
{
    /**
     * Associative array such as `'property' => 'value'` with all boundary values
     * @return array
     */
    public function values(): array;

    /**
     * Get a DTO value by property
     * @param string $property
     * @return mixed
     */
    public function get(string $property): mixed;

    /**
     * Rules to validate DTO
     * @return array ['fieldName' => ValidatorFieldCollection]
     */
    public function rules(): array;
}
