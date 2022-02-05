<?php

declare(strict_types=1);

namespace Difra\Cache\Adapter;

use Difra\Cache;
use Difra\Exception;

/**
 * Memcached (memcached module) adapter
 * Class MemCached
 * @package Difra\Cache
 */
class MemCached extends Common
{
    /** @var \Memcached|bool|null */
    private static \Memcached|bool|null $memcache = null;
    /** @var bool Serialize data flag */
    private static bool $serialize = true;
    /** @var int TTL */
    private static int $lifetime = 0;
    /** @var string|null Adapter name */
    public ?string $adapter = Cache::INST_MEMCACHED;

    /**
     * Detect if backend is available
     * @return bool
     */
    public static function isAvailable(): bool
    {
        try {
            if (!is_null(self::$memcache)) {
                return (bool)self::$memcache;
            }
            if (!extension_loaded('memcached')) {
                return self::$memcache = false;
            }

            $memcache = new \MemCached();
            // todo: load from config
            $memcache->addServer('127.0.0.1', 11211);
            // if ($memcache->getStats() < 0) { // returns ['127.0.0.1:11211'=>['pid'=>-1,...]]
            //     return self::$memcache = false;
            // }
            self::$memcache = $memcache;

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Get cache record implementation
     * @param string $id
     * @param bool $doNotTestCacheValidity
     * @return mixed
     */
    public function realGet(string $id, bool $doNotTestCacheValidity = false): mixed
    {
        $data = self::$memcache->get($id);
        if ($data && is_string($data) && self::$serialize) {
            return @unserialize($data);
        }
        return $data;
    }

    /**
     * Test if cache record exists implementation
     * @param string $id
     * @return bool
     * @deprecated
     */
    public function test(string $id): bool
    {
        $data = $this->get($id);
        return !empty($data);
    }

    /**
     * Put cache record implementation
     * @param string $id
     * @param mixed $data
     * @param int|null $specificLifetime
     * @return bool
     */
    public function realPut(string $id, mixed $data, int $specificLifetime = null): bool
    {
        return self::$memcache->set(
            $id,
            self::$serialize ? serialize($data) : $data,
            $specificLifetime ?: self::$lifetime
        );
    }

    /**
     * Delete cache record implementation
     * @param string $id
     * @return void
     */
    public function realRemove(string $id): void
    {
        @self::$memcache->delete($id);
    }

    /**
     * Define automatic cache cleaning as available
     * @return bool
     */
    public function isAutomaticCleaningAvailable(): bool
    {
        return true;
    }
}
