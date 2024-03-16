<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Contracts;

use Astrotech\ApiBase\Adapter\Exception\InvalidDtoParamException;

interface Dto
{
    /**
     * Associative array such as `'property' => 'value'` with all boundary values
     * @return array
     */
    public function values(): array;

    /**
     * Gets the value of a DTO attribute.
     * @param string $property The name of the attribute.
     * @return mixed The value of the attribute.
     * @throws InvalidDtoParamException If the attribute does not exist in the DTO.
     */
    public function get(string $property): mixed;
}
