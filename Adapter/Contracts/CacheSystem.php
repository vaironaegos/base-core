<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Contracts;

interface CacheSystem
{
    public const TWO_MINUTES = 2 * 60;
    public const FIVE_MINUTES = 5 * 60;
    public const TEN_MINUTES = 10 * 60;
    public const FIFTEEN_MINUTES = 15 * 60;
    public const THIRTY_MINUTES = 30 * 60;
    public const FORTY_MINUTES = 40 * 60;
    public const ONE_HOUR = 24 * 60 * 60;
    public const TWO_HOURS = 2 * 24 * 60 * 60;
    public const THREE_HOURS = 3 * 24 * 60 * 60;
    public const FIVE_HOURS = 5 * 24 * 60 * 60;
    public const FIFTEEN_HOURS = 15 * 24 * 60 * 60;

    /**
     * Verify if cache with given key exists in cache system
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Get the value from cache system with given key
     * @param string $key
     * @return string
     */
    public function get(string $key): string;

    /**
     * Set a value into cache system with given key and value
     * @param string $key
     * @param string $value
     * @param int|null $durationInSecs
     * @return void
     */
    public function set(string $key, string $value, int $durationInSecs = null): void;

    /**
     * Remove a value from cache system with given key
     * @param string $key
     * @return void
     */
    public function destroy(string $key): void;

    /**
     * Clear all cache system values
     * @return void
     */
    public function clearAll(): void;
}
