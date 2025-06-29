<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Category\ModelWithCategoryRepository;
use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryTrait;

/**
 * @extends  ModelWithCategoryRepository<Photoalbum>
 */
final class PhotoalbumRepository extends ModelWithCategoryRepository
{
    protected const UNDERLYING_CLASS = Photoalbum::class;

    use RepositoryTrait;

    public function __construct(
        protected readonly Connection $connection,
        protected readonly GenericRepository $genericRepository,
    ) {

    }
}
