<?php

declare(strict_types=1);

namespace Difra\Cache;

use Difra\Cache;

/**
 * Stub cache adapter
 * Class None
 * @package Difra\Cache
 */
class None extends Common
{
    /** @var string|null Adapter name */
    public ?string $adapter = Cache::INST_NONE;

    /**
     * Stub backend is always available. Or not.
     * Depends on your point of view, but let adapter be available anyways.
     * @return bool
     */
    public static function isAvailable(): bool
    {
        return true;
    }

    /**
     * Get cache record pseudo-implementation
     * @param string $id
     * @param bool $doNotTestCacheValidity
     * @return string
     */
    public function realGet(string $id, bool $doNotTestCacheValidity = false): mixed
    {
        return null;
    }

    /**
     * Test if cache record exists pseudo-implementation
     * @param string $id cache id
     * @return bool
     */
    public function test(string $id): bool
    {
        return false;
    }

    /**
     * Set cache record pseudo-implementation
     * @param string $id
     * @param string $data
     * @param int|null $specificLifetime
     * @return bool
     */
    public function realPut(string $id, mixed $data, int|null $specificLifetime = null): bool
    {
        return false;
    }

    /**
     * Delete cache record pseudo-implementation
     * @param string $id
     */
    public function realRemove(string $id): void
    {
    }

    /**
     * Let it be
     * @return bool
     */
    public function isAutomaticCleaningAvailable(): bool
    {
        return true;
    }
}
