<?php

declare(strict_types=1);

namespace Difra\Cache\Adapter;

use Difra\Cache;
use Difra\Cache\CacheInterface;
use Difra\Envi;
use Difra\Exception;

/**
 * Abstract cache adapter class
 */
abstract class Common implements CacheInterface
{
    //abstract static public function isAvailable();

    /** @var string|null */
    public ?string $adapter = null;

    /** @var string|null Version for cache */
    private ?string $version = null;
    /** @var string|null Cache prefix */
    private ?string $prefix = null;
    /** @var string Session prefix */
    private string $sessionPrefix = 'session:';

    /**
     * Constructor
     * @throws \Difra\Exception
     */
    public function __construct()
    {
        if (!method_exists($this, 'isAvailable') or !$this::isAvailable()) {
            throw new Exception(__CLASS__ . ' requested, but that cache is not available!');
        }
        $this->version = Envi\Version::getBuild();
        $this->prefix = Envi::getSubsite() . ':';
        $this->sessionPrefix = 'session:';
    }

    /**
     * Check if cache record exists
     * @param string $id
     * @return bool
     * @deprecated
     */
    abstract public function test(string $id): bool;

    /**
     * Defines if cache backend supports automatic cleaning
     * @return bool
     */
    abstract public function isAutomaticCleaningAvailable(): bool;

    /**
     * Get cache record wrapper
     * @param $key
     * @param bool $versionCheck Check if version number changed
     * @return mixed
     */
    public function get($key, bool $versionCheck = true): mixed
    {
        $data = $this->realGet($this->prefix . $key);
        if (!$data ||
            !isset($data['expires']) or $data['expires'] < time() ||
            ($versionCheck && (!isset($data['version']) || $data['version'] != $this->version))
        ) {
            return null;
        }
        return $data['data'];
    }

    /**
     * Get cache record
     * @param string $id
     * @param bool $doNotTestCacheValidity
     * @return mixed
     */
    abstract public function realGet(string $id, bool $doNotTestCacheValidity = false): mixed;

    /**
     * Set cache record wrapper
     * @param string $key
     * @param mixed $data
     * @param int $ttl
     */
    public function put(string $key, mixed $data, int $ttl = self::DEFAULT_TTL): void
    {
        $data = [
            'expires' => time() + $ttl,
            'data' => $data,
            'version' => $this->version
        ];
        $this->realPut($this->prefix . $key, $data, $ttl);
    }

    /**
     * Set cache record
     * @param string $id
     * @param mixed $data
     * @param ?int $specificLifetime
     */
    abstract public function realPut(string $id, mixed $data, ?int $specificLifetime = null);

    /**
     * Delete cache record wrapper
     * @param string $key
     */
    public function remove(string $key): void
    {
        $this->realRemove($this->prefix . $key);
    }

    /**
     * Delete cache method
     * @param string $id
     */
    abstract public function realRemove(string $id): void;

    /**
     * Set session handler to use current cache, if available
     * @throws \Difra\Cache\Exception
     */
    public function setSessionsInCache()
    {
        static $set = false;
        if ($set) {
            return;
        }
        if (Cache::getInstance()->adapter === Cache::INST_NONE) {
            return;
        }

        \session_set_save_handler(
        // open
            function ($s, $n) {
                return true;
            },
            // close
            function () {
                return true;
            },
            // read
            function ($id) {
                return Cache::getInstance()->get($this->sessionPrefix . $id, false) ?: '';
            },
            // write
            function ($id, $data) {
                if (!$data) {
                    return false;
                }
                Cache::getInstance()->put($this->sessionPrefix . $id, $data, 86400); // 24h
                return true;
            },
            // destroy
            function ($id) {
                Cache::getInstance()->remove($this->sessionPrefix . $id);
                return true;
            },
            // garbage collector
            function ($expire) {
                return true;
            }
        );
        $set = true;
    }
}
