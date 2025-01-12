<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

trait RepositoryTrait
{
    public function fetchById(int $id): Model|null
    {
        return $this->genericRepository->fetchById(self::UNDERLYING_CLASS, $id);
    }

    public function save(Model $model): void
    {
        $this->genericRepository->save($model);
    }

    public function fetchAll(array $where = [], array $args = [], string $afterWhere = ''): array
    {
        return $this->genericRepository->fetchAll(self::UNDERLYING_CLASS, $where, $args, $afterWhere);
    }
}
