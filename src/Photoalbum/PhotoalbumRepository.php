<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Category\ModelWithCategoryRepository;
use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

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
