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

    public function delete(Model $model): void
    {
        $this->genericRepository->delete($model);
    }

    public function deleteById(int $id): void
    {
        $this->genericRepository->deleteById(self::UNDERLYING_CLASS, $id);
    }

    public function fetch(array $where = [], array $args = [], string $afterWhere = ''): Model|null
    {
        return $this->genericRepository->fetch(self::UNDERLYING_CLASS, $where, $args, $afterWhere);
    }

    public function fetchAll(array $where = [], array $args = [], string $afterWhere = ''): array
    {
        return $this->genericRepository->fetchAll(self::UNDERLYING_CLASS, $where, $args, $afterWhere);
    }

    public function fetchAllForSelect(): array
    {
        return $this->genericRepository->fetchAllForSelect(self::UNDERLYING_CLASS);
    }
}
