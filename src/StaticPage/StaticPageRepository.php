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

    public function save(Model $model): void
    {
        $oldData = null;
        if ($model->id !== null)
        {
            $oldData = $this->fetchById($model->id);
        }
        $this->genericRepository->save($model);

        if ($oldData)
        {
            $this->connection->executeQuery('REPLACE INTO sub_backups(`id`, `name`, `text`) VALUES (?,?,?)', [$oldData->id, $oldData->name, $oldData->text]);
        }
    }

    public function hasBackup(StaticPageModel $model): bool
    {
        return !!$this->connection->doQueryAndFetchOne('SELECT * FROM sub_backups WHERE id= ?', [$model->id]);
    }
}
