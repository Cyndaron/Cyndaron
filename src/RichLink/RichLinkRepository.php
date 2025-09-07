<?php
declare(strict_types=1);

namespace Cyndaron\RichLink;

use Cyndaron\Category\ModelWithCategoryRepository;
use Cyndaron\Category\ModelWithCategoryRepositoryTrait;
use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryTrait;

/**
 * @implements ModelWithCategoryRepository<RichLink>
 */
final class RichLinkRepository implements ModelWithCategoryRepository
{
    private const UNDERLYING_CLASS = RichLink::class;

    /** @use ModelWithCategoryRepositoryTrait<RichLink> */
    use ModelWithCategoryRepositoryTrait;
    use RepositoryTrait;

    public function __construct(
        protected readonly Connection $connection,
        protected readonly GenericRepository $genericRepository,
    ) {

    }
}
