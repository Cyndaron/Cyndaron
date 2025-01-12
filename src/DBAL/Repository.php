<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

use PDOException;

final class Repository
{
    public function __construct()
    {
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param int|null $id
     * @return T
     */
    public function fetchOrCreate(string $class, int|null $id): Model
    {
        if ($id === null)
        {
            return new $class();
        }

        $ret = $this->tryFetchById($class, $id);
        if ($ret === null)
        {
            return new $class($id);
        }

        return $ret;
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param int $id
     * @return T|null
     */
    public function tryFetchById(string $class, int $id): Model|null
    {
        return $class::fetchById($id);
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param int $id
     * @return T
     */
    public function fetchById(string $class, int $id): Model
    {
        $ret = $this->tryFetchById($class, $id);
        if ($ret === null)
        {
            throw new \RuntimeException('No such entity!');
        }

        return $ret;
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @return T[]
     */
    public function fetchAll(string $class): array
    {
        return $class::fetchAll();
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param int $id
     */
    public function deleteById(string $class, int $id): void
    {
        $model = new $class($id);
        $this->delete($model);
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }

    /**
     * @param Model $model
     * @throws PDOException
     * @return void
     */
    public function save(Model $model): void
    {
        $model->save();
    }
}
