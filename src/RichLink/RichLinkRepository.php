<?php
declare(strict_types=1);

namespace Cyndaron\RichLink;

use Cyndaron\Category\ModelWithCategoryRepository;
use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryTrait;

/**
 * @extends  ModelWithCategoryRepository<RichLink>
 */
final class RichLinkRepository extends ModelWithCategoryRepository
{
    protected const UNDERLYING_CLASS = RichLink::class;

    use RepositoryTrait;

    public function __construct(
        protected readonly Connection $connection,
        protected readonly GenericRepository $genericRepository,
    ) {

    }
}
