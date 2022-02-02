<?php

declare(strict_types=1);

namespace Difra\Cache;

interface CacheInterface
{
    public const DEFAULT_TTL = 300;

    public function get($key, bool $versionCheck = true): mixed;
    public function put(string $key, mixed $data, int $ttl = self::DEFAULT_TTL): void;
    public function remove(string $key): void;
}