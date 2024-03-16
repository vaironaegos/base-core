<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Contracts;

interface UuidTools
{
    /**
     * Generates a new UUID (Universally Unique Identifier) string.
     * @return string The generated UUID string.
     */
    public function create(): string;

    /**
     * Converts a UUID string to its binary representation.
     * @param string $uuid The UUID string to convert.
     * @return string The binary representation of the UUID.
     */
    public function toBytes(string $uuid): string;

    /**
     * Converts a binary representation of a UUID to its string representation.
     * @param string $binary The binary representation of the UUID.
     * @return string The UUID string representation.
     */
    public function toUuid(string $binary): string;
}
