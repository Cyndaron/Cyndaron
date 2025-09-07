<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Category\ModelWithCategoryRepository;
use Cyndaron\Category\ModelWithCategoryRepositoryTrait;
use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryTrait;

/**
 * @implements ModelWithCategoryRepository<Photoalbum>
 */
final class PhotoalbumRepository implements ModelWithCategoryRepository
{
    private const UNDERLYING_CLASS = Photoalbum::class;

    /** @use ModelWithCategoryRepositoryTrait<Photoalbum> */
    use ModelWithCategoryRepositoryTrait;
    use RepositoryTrait;

    public function __construct(
        protected readonly Connection $connection,
        protected readonly GenericRepository $genericRepository,
    ) {

    }
}
