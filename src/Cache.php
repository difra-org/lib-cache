<?php

declare(strict_types=1);

namespace Difra;

use Difra\Cache\CacheInterface;

/**
 * Cache factory
 * Class Cache
 * @package Difra
 */
class Cache
{
    /** Auto detect */
    public const INST_AUTO = 'Auto';
    /** Memcached module */
    public const INST_MEMCACHED = 'MemCached';
    /** Memcache module */
    public const INST_MEMCACHE = 'Memcache';
    /** APCu */
    public const INST_APCU = 'APCu';
    /** Stub */
    public const INST_NONE = 'None';
    /** Default */
    public const INST_DEFAULT = self::INST_AUTO;
    /** Default TTL (seconds) */
    public const DEFAULT_TTL = 300;
    /**
     * Configured cache adapters.
     * @var array
     */
    private static array $adapters = [];

    /**
     * Builds new cache adapter or returns
     * existing one.
     * @param string $configName
     * @return \Difra\Cache\CacheInterface
     * @throws \Difra\Cache\Exception
     */
    public static function getInstance(string $configName = self::INST_DEFAULT): CacheInterface
    {
        if ($configName == self::INST_AUTO) {
            $configName = self::detect();
        }
        return self::getAdapter($configName);
    }

    /**
     * Detect available adapter
     * @return string
     */
    private static function detect(): string
    {
        static $autoDetected = null;
        if ($autoDetected) {
            return $autoDetected;
        }
        if (!Debugger::isCachesEnabled()) {
            Debugger::addLine('Caching disabled by Debug Mode settings');
            return $autoDetected = self::INST_NONE;
        }
        if (Cache\Adapter\APCu::isAvailable()) {
            Debugger::addLine('Auto-detected cache type: APCu');
            return $autoDetected = self::INST_APCU;
        } elseif (Cache\Adapter\MemCached::isAvailable()) {
            Debugger::addLine('Auto-detected cache type: MemCached');
            return $autoDetected = self::INST_MEMCACHED;
        } elseif (Cache\Adapter\MemCache::isAvailable()) {
            Debugger::addLine('Auto-detected cache type: Memcache');
            return $autoDetected = self::INST_MEMCACHE;
        }
        Debugger::addLine('No cache detected');
        return $autoDetected = self::INST_NONE;
    }

    /**
     * Factory
     * @param string $configName
     * @return \Difra\Cache\CacheInterface
     * @throws \Difra\Cache\Exception
     */
    private static function getAdapter(string $configName): CacheInterface
    {
        if (isset(self::$adapters[$configName])) {
            return self::$adapters[$configName];
        }

        return match ($configName) {
            self::INST_APCU => self::$adapters[$configName] = new Cache\Adapter\APCu(),
            self::INST_MEMCACHED => self::$adapters[$configName] = new Cache\Adapter\MemCached(),
            self::INST_MEMCACHE => self::$adapters[$configName] = new Cache\Adapter\MemCache(),
            self::INST_NONE => self::$adapters[$configName] = new Cache\Adapter\None(),
            default => throw new \Difra\Cache\Exception("Unknown cache adapter type: $configName"),
        };
    }
}
