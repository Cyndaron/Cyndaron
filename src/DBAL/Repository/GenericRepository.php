<?php
declare(strict_types=1);

namespace Cyndaron\DBAL\Repository;

use Cyndaron\DBAL\Model;
use PDOException;

interface GenericRepository
{
    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return T|null
     */
    public function fetch(string $class, array $where = [], array $args = [], string $afterWhere = ''): Model|null;

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param int|null $id
     * @return T
     */
    public function fetchOrCreate(string $class, int|null $id): Model;

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param int $id
     * @return T|null
     */
    public function fetchById(string $class, int $id): Model|null;

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return T[]
     */
    public function fetchAll(string $class, array $where = [], array $args = [], string $afterWhere = ''): array;

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @return T[]
     */
    public function fetchAllForSelect(string $class): array;

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param int $id
     */
    public function deleteById(string $class, int $id): void;

    public function delete(Model $model): void;

    /**
     * @param Model $model
     * @throws PDOException
     * @return void
     */
    public function save(Model $model): void;

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param array<string, float|int|string|null> $array
     * @return T
     */
    public function createFromArray(string $class, array $array): Model;
}
