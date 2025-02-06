<?php
declare(strict_types=1);

namespace Cyndaron\StaticPage;

use Cyndaron\Category\ModelWithCategoryRepository;
use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\RepositoryTrait;

/**
 * @extends ModelWithCategoryRepository<StaticPageModel>
 */
final class StaticPageRepository extends ModelWithCategoryRepository
{
    protected const UNDERLYING_CLASS = StaticPageModel::class;

    use RepositoryTrait;

    public function __construct(
        protected readonly Connection $connection,
        protected readonly GenericRepository $genericRepository,
    ) {

    }

    public function delete(Model $model): void
    {
        $this->connection->executeQuery('DELETE FROM sub_backups WHERE id = ?', [$model->id]);
        $this->genericRepository->delete($model);
    }
}
