<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

trait RepositoryTrait
{
    public function fetchById(int $id): Model|null
    {
        return $this->genericRepository->fetchById(self::UNDERLYING_CLASS, $id);
    }
}
