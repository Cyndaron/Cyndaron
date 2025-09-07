<?php
declare(strict_types=1);

namespace Cyndaron\Category;

use Cyndaron\DBAL\Repository\RepositoryInterface;

/**
 * @template T of ModelWithCategory
 * @extends RepositoryInterface<T>
 */
interface ModelWithCategoryRepository extends RepositoryInterface
{
    /**
     * @param Category $category
     * @param string $afterWhere
     * @throws \Exception
     * @return T[]
     */
    public function fetchAllByCategory(Category $category, string $afterWhere = ''): array;

    /**
     * @return T[]
     */
    public function fetchAllAndSortByName(): array;

    /**
     * @param T $object
     */
    public function linkToCategory(ModelWithCategory $object, Category $category, int $priority = 0): void;

    /**
     * @param T $object
     */
    public function unlinkFromCategory(ModelWithCategory $object, Category $category): void;

    /**
     * @param T $object
     * @throws \Exception
     * @return Category|null
     */
    public function getFirstLinkedCategory(ModelWithCategory $object): Category|null;

    /**
     * @param T $object
     * @throws \Exception
     * @return Category[]
     */
    public function getLinkedCategories(ModelWithCategory $object): array;

    /**
     * @param T $object
     * @throws \Exception
     * @return int[]
     */
    public function getLinkedCategoryIds(ModelWithCategory $object): array;
}
