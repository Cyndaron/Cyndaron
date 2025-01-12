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
     * @param int $id
     * @return T|null
     */
    public function fetchById(int $id): Model|null;

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
}
