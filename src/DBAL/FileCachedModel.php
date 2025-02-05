<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

use Cyndaron\Util\FileCache;
use function assert;

trait FileCachedModel
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
        $objects = parent::fetchAll();
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

    /**
     * @inheritDoc
     *
     * @deprecated
     */
    public static function fetchById(int $id): static|null
    {
        self::loadCache();
        return self::$cache[static::TABLE][$id] ?? null;
    }

    /**
     * @inheritDoc
     *
     * @deprecated
     */
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

    /**
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return static[]
     *
     * @deprecated
     */
    public static function fetchAll(array $where = [], array $args = [], string $afterWhere = ''): array
    {
        if ($where !== [] || $args !== [] || $afterWhere !== '')
        {
            return parent::fetchAll($where, $args, $afterWhere);
        }

        self::loadCache();
        return self::$cache[static::TABLE];
    }
}
