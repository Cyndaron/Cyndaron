<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

use PDOException;

/**
 * @template T of Model
 */
interface RepositoryInterface
{
    /**
     * @param int|null $id
     * @return T
     */
    public function fetchOrCreate(int|null $id): Model;

    /**
     * @param int $id
     * @return T|null
     */
    public function fetchById(int $id): Model|null;

    /**
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return T|null
     */
    public function fetch(array $where = [], array $args = [], string $afterWhere = ''): Model|null;

    /**
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return T[]
     */
    public function fetchAll(array $where = [], array $args = [], string $afterWhere = ''): array;

    /**
     * @param T $model
     * @throws PDOException
     * @return void
     */
    public function save(Model $model): void;

    /**
     * @param T $model
     * @throws PDOException
     * @return void
     */
    public function delete(Model $model): void;

    /**
     * @return T[]
     */
    public function fetchAllForSelect(): array;

    /**
     * @param array<string, float|int|string|null> $array
     * @return T
     */
    public function createFromArray(array $array): Model;
}
