<?php
declare(strict_types=1);

namespace Cyndaron\DBAL\Repository;

use Cyndaron\DBAL\Model as T;
use PDOException;

/**
 * @template T of T
 */
interface RepositoryInterface
{
    /**
     * @param int|null $id
     * @return T
     */
    public function fetchOrCreate(int|null $id): T;

    /**
     * @param int $id
     * @return T|null
     */
    public function fetchById(int $id): T|null;

    /**
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return T|null
     */
    public function fetch(array $where = [], array $args = [], string $afterWhere = ''): T|null;

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
    public function save(T $model): void;

    /**
     * @param T $model
     * @throws PDOException
     * @return void
     */
    public function delete(T $model): void;

    /**
     * @return T[]
     */
    public function fetchAllForSelect(): array;

    /**
     * @param array<string, float|int|string|null> $array
     * @return T
     */
    public function createFromArray(array $array): T;
}
