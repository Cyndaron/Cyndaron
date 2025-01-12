<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

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
}
