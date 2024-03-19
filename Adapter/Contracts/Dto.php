<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts;

use Astrotech\Core\Base\Adapter\Exception\InvalidDtoParamException;

interface Dto
{
    /**
     * Associative array in the format `'property' => 'value'` with
     * all raw values without transformations
     * @return array
     */
    public function values(): array;

    /**
     * Associative array in the format `'property' => 'value'` with
     * all values being able to be transformed or mutated for certain scenarios
     * @return array
     */
    public function toArray(): array;

    /**
     * Gets the value of a DTO attribute.
     * @param string $property The name of the attribute.
     * @return mixed The value of the attribute.
     * @throws InvalidDtoParamException If the attribute does not exist in the DTO.
     */
    public function get(string $property): mixed;
}
