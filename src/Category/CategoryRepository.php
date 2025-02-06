<?php
declare(strict_types=1);

namespace Cyndaron\Category;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;
use Cyndaron\Photoalbum\PhotoalbumRepository;
use Cyndaron\RichLink\RichLinkRepository;
use Cyndaron\StaticPage\StaticPageRepository;
use function array_merge;
use function strcasecmp;
use function usort;

/**
 * @extends  ModelWithCategoryRepository<Category>
 */
final class CategoryRepository extends ModelWithCategoryRepository
{
    protected const UNDERLYING_CLASS = Category::class;

    use RepositoryTrait;

    public function __construct(
        protected readonly Connection $connection,
        protected readonly GenericRepository $genericRepository,
        private readonly StaticPageRepository $staticPageRepository,
        private readonly PhotoalbumRepository $photoalbumRepository,
        private readonly RichLinkRepository $richLinkRepository,
    ) {

    }

    /**
     * @param Category $category
     * @param string $orderBy
     * @throws \Exception
     * @return ModelWithCategory[]
     */
    public function getUnderlyingPages(Category $category, string $orderBy = ''): array
    {
        $ret = array_merge(
            $this->staticPageRepository->fetchAllByCategory($category),
            $this->fetchAllByCategory($category),
            $this->photoalbumRepository->fetchAllByCategory($category),
            $this->richLinkRepository->fetchAllByCategory($category),
        );

        if ($orderBy === 'name')
        {
            usort($ret, static function(ModelWithCategory $m1, ModelWithCategory $m2)
            {
                return strcasecmp($m1->name, $m2->name);
            });
        }
        else
        {
            usort($ret, static function(ModelWithCategory $m1, ModelWithCategory $m2)
            {
                $prio = ($m1->priority <=> $m2->priority);
                if ($prio !== 0)
                {
                    return $prio;
                }

                // In the case of the same priority: newest first.
                return $m2->created <=> $m1->created;
            });
        }

        return $ret;
    }
}
