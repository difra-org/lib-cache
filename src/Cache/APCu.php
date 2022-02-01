<?php

declare(strict_types=1);

namespace Difra\Cache;

use Difra\Cache;

class APCu extends Common
{
    /** @var string|null Adapter name */
    public ?string $adapter = Cache::INST_APCU;

    /**
     * Is APCu available?
     * @return bool
     */
    public static function isAvailable(): bool
    {
        try {
            if (!extension_loaded('apcu') || php_sapi_name() === 'cli' || !function_exists('apcu_sma_info')) {
                return false;
            }
            $info = @apcu_sma_info(true);
            if (($error = error_get_last()) && $error['file'] === __FILE__) {
                return false;
            }
            if (empty($info) || empty($info['num_seg'])) {
                return false;
            }
        } catch (\Exception) {
            return false;
        }
        return true;
    }

    /**
     * Check if cache record exists
     * @param string $id
     * @return bool
     * @deprecated
     */
    public function test(string $id): bool
    {
        return apcu_exists($id);
    }

    /**
     * Defines if cache backend supports automatic cleaning
     * @return bool
     */
    public function isAutomaticCleaningAvailable(): bool
    {
        return true;
    }

    /**
     * Get cache record
     * @param string $id
     * @param bool $doNotTestCacheValidity
     * @return mixed
     */
    public function realGet(string $id, bool $doNotTestCacheValidity = false): mixed
    {
        $success = false;
        $value = apcu_fetch($id, $success);
        return $success ? $value : null;
    }

    /**
     * Set cache record
     * @param string $id
     * @param mixed $data
     * @param int|null $specificLifetime
     */
    public function realPut(string $id, mixed $data, ?int $specificLifetime = null)
    {
        apcu_store($id, $data, $specificLifetime ?: Cache::DEFAULT_TTL);
    }

    /**
     * Delete cache method
     * @param string $id
     */
    public function realRemove(string $id): void
    {
        apcu_delete($id);
    }
}
