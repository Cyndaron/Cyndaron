<?php
declare(strict_types=1);

namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

/**
 * @implements RepositoryInterface<StaticPageModel>
 */
final class StaticPageRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = StaticPageModel::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository, private readonly Connection $connection)
    {

    }

    public function delete(Model $model): void
    {
        $this->connection->executeQuery('DELETE FROM sub_backups WHERE id = ?', [$model->id]);
        $this->genericRepository->delete($model);
    }
}
