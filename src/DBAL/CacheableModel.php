<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

use Cyndaron\Util\FileCache;
use function assert;

abstract class CacheableModel extends Model
{
    /** @var array<string, static[]> */
    protected static array $cache = [];

    protected static function getCacheKey(): string
    {
        return 'database-' . static::TABLE;
    }

    protected static function saveCache(): void
    {
        $cacheKey = self::getCacheKey();
        $handle = new FileCache($cacheKey, [static::class]);
        $handle->save(self::$cache[static::TABLE]);
    }

    protected static function createCache(): void
    {
        $objects = static::fetchAll();
        foreach ($objects as $object)
        {
            assert($object->id !== null);
            // @phpstan-ignore-next-line
            self::$cache[static::TABLE][$object->id] = $object;
        }
    }

    protected static function loadCache(bool $force = false): void
    {
        if (!empty(self::$cache[static::TABLE]) && !$force)
        {
            return;
        }

        // @phpstan-ignore-next-line
        self::$cache[static::TABLE] = [];
        $handle = new FileCache(self::getCacheKey(), [static::class]);
        $handle->load(self::$cache[static::TABLE]);

        self::createCache();
        self::saveCache();
    }

    public static function fetchById(int $id): ?static
    {
        self::loadCache();
        return self::$cache[static::TABLE][$id] ?? null;
    }

    public function save(): bool
    {
        self::loadCache();
        $result = parent::save();
        if ($result)
        {
            // @phpstan-ignore-next-line
            self::$cache[static::TABLE][$this->id] = $this;
            self::saveCache();
        }

        return $result;
    }
}
