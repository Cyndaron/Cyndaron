<?php
declare(strict_types=1);

namespace Cyndaron\DBAL\Repository;

use Cyndaron\DBAL\Model;
use Cyndaron\Util\FileCache;
use Cyndaron\Util\FileCacheLoadResult;
use function assert;
use function array_key_exists;
use function is_string;

final class FileCachedRepository implements GenericRepository
{
    use GenericRepositoryTrait;

    /**
     * Indexed by database table name, then by ID in that table
     *
     * @var array<string, array<int, Model>>
     */
    private array $cache = [];

    public function __construct(
        private readonly DatabaseRepository $nonCached
    ) {
    }

    /**
     * @param class-string<Model> $class
     * @return string
     */
    private function getCacheKey(string $class): string
    {
        return 'database-' . $class::TABLE;
    }

    /**
     * @param class-string<Model> $class
     * @return void
     */
    private function saveCache(string $class): void
    {
        $cacheKey = $this->getCacheKey($class);
        $handle = new FileCache($cacheKey, true);
        $handle->save($this->cache[$class::TABLE]);
    }

    /**
     * @param class-string<Model> $class
     * @return void
     */
    private function createCache(string $class): void
    {
        $objects = $this->nonCached->fetchAll($class);
        foreach ($objects as $object)
        {
            assert($object->id !== null);
            assert(is_string($class::TABLE));
            $this->cache[$class::TABLE][$object->id] = $object;
        }
    }

    /**
     * @param class-string<Model> $class
     * @return void
     */
    private function loadCache(string $class): void
    {
        if (!empty($this->cache[$class::TABLE]))
        {
            return;
        }

        // @phpstan-ignore-next-line
        $this->cache[$class::TABLE] = [];
        $handle = new FileCache($this->getCacheKey($class), true);
        $loadResult = $handle->load($this->cache[$class::TABLE]);
        if ($loadResult !== FileCacheLoadResult::OK)
        {
            $this->createCache($class);
            $this->saveCache($class);
        }
    }

    /**
     * @inheritDoc
     */
    public function fetchById(string $class, int $id): Model|null
    {
        $this->loadCache($class);

        // @phpstan-ignore-next-line
        return $this->cache[$class::TABLE][$id] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function fetchAll(string $class, array $where = [], array $args = [], string $afterWhere = ''): array
    {
        if ($where !== [] || $args !== [] || $afterWhere !== '')
        {
            return $this->nonCached->fetchAll($class, $where, $args, $afterWhere);
        }

        $this->loadCache($class);
        // @phpstan-ignore-next-line
        return $this->cache[$class::TABLE];
    }

    public function delete(Model $model): void
    {
        if ($model->id !== null && array_key_exists($model::TABLE, $this->cache) && array_key_exists($model->id, $this->cache[$model::TABLE]))
        {
            unset($this->cache[$model::TABLE][$model->id]);
        }

        $this->nonCached->delete($model);
    }

    /**
     * @inheritDoc
     */
    public function save(Model $model): void
    {
        $this->nonCached->save($model);

        $this->loadCache($model::class);
        // @phpstan-ignore-next-line
        $this->cache[$model::TABLE][$model->id] = $model;
        $this->saveCache($model::class);
    }
}
