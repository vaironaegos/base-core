<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Contracts;

interface ValueObject
{
    /**
     * Raw value object value
     * @return string|int|float|bool
     */
    public function value(): string|int|float|bool;

    /**
     * Method to compare equality between two value objects
     * @param ValueObject $valueObject
     * @return bool
     */
    public function isEqualsTo(ValueObject $valueObject): bool;

    /**
     * Method to return a object as string
     * @return string
     */
    public function __toString(): string;

    /**
     * Method to generate the Value object Hash
     * @return string
     */
    public function objectHash(): string;
}
