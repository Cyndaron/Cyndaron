<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

use Cyndaron\Util\Util;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function serialize;
use function unserialize;
use const ROOT_DIR;

abstract class CacheableModel extends Model
{
    /** @var array<string, static[]> */
    protected static array $cache = [];

    protected const CACHE_DIR = ROOT_DIR . '/cache/cyndaron';

    protected static function getCacheFile(): string
    {
        return self::CACHE_DIR . '/database-' . static::TABLE . '.phps';
    }

    protected static function saveCacheToFile(): void
    {
        $cacheFile = self::getCacheFile();
        Util::ensureDirectoryExists(self::CACHE_DIR);
        file_put_contents($cacheFile, serialize(self::$cache[static::TABLE]));
    }

    protected static function createCache(): void
    {
        $objects = self::fetchAll();
        foreach ($objects as $object)
        {
            assert($object->id !== null);
            self::$cache[static::TABLE][$object->id] = $object;
        }
    }

    protected static function loadCache(bool $force = false): void
    {
        if (!empty(self::$cache[static::TABLE]) && !$force)
        {
            return;
        }

        $cacheFile = self::getCacheFile();
        if (file_exists($cacheFile))
        {
            $serialized = file_get_contents($cacheFile);
            if ($serialized)
            {
                $unserialized = unserialize($serialized, [static::class]);
                if ($unserialized)
                {
                    self::$cache[static::TABLE] = $unserialized;
                    return;
                }
            }
        }

        self::createCache();
        self::saveCacheToFile();
    }

    public static function fetchById(int $id): ?static
    {
        self::loadCache();
        return self::$cache[static::TABLE][$id] ?? null;
    }

    public function save(): bool
    {
        $result = parent::save();
        if ($result)
        {
            self::$cache[static::TABLE][$this->id] = $this;
            self::saveCacheToFile();
        }

        return $result;
    }
}
