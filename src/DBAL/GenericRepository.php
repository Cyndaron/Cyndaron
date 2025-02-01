<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

use PDOException;

final class GenericRepository
{
    public function __construct(private readonly Connection $connection)
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

        $ret = $this->fetchById($class, $id);
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
    public function fetchById(string $class, int $id): Model|null
    {
        return $class::fetchById($id);
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return T[]
     */
    public function fetchAll(string $class, array $where = [], array $args = [], string $afterWhere = ''): array
    {
        return $class::fetchAll();
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @return T[]
     */
    public function fetchAllForSelect(string $class): array
    {
        $records = $this->fetchAll($class);
        $ret = [];
        foreach ($records as $record)
        {
            $ret[$record->id] = $record;
        }

        return $ret;
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
        if (!$model->id)
        {
            throw new DatabaseError('No ID!');
        }

        $table = $model::TABLE;
        /** @noinspection SqlResolve */
        $this->connection->executeQuery("DELETE FROM {$table} WHERE id = ?", [$model->id]);
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
