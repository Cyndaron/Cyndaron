<?php
declare(strict_types=1);

namespace Cyndaron\Category;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use function assert;
use function usort;
use function reset;

/**
 * @template T of ModelWithCategory
 * @implements RepositoryInterface<T>
 * @property Connection $connection
 * @property GenericRepository $genericRepository
 */
abstract class ModelWithCategoryRepository implements RepositoryInterface
{
    protected const UNDERLYING_CLASS = '';

    /**
     * @param Category $category
     * @param string $afterWhere
     * @throws \Exception
     * @return T[]
     */
    public function fetchAllByCategory(Category $category, string $afterWhere = ''): array
    {
        $tableName = static::UNDERLYING_CLASS::CATEGORY_TABLE;
        /** @noinspection SqlResolve */
        $entries = $this->connection->doQueryAndFetchAll("SELECT DISTINCT id, priority FROM {$tableName} WHERE categoryId = ? " . $afterWhere, [$category->id]) ?: [];
        $ret = [];
        foreach ($entries as $entry)
        {
            /** @var int $id */
            $id = $entry['id'];
            $model = $this->fetchById($id);
            assert($model instanceof ModelWithCategory);
            $model->priority = (int)$entry['priority'];
            $ret[] = $model;
        }

        return $ret;
    }

    /**
     * @return T[]
     */
    public function fetchAllAndSortByName(): array
    {
        /** @var T[] $entries */
        $entries = $this->fetchAll();
        usort($entries, static function(ModelWithCategory $entry1, ModelWithCategory $entry2)
        {
            return $entry1->name <=> $entry2->name;
        });
        return $entries;
    }

    /**
     * @param T $object
     */
    public function linkToCategory(ModelWithCategory $object, Category $category, int $priority = 0): void
    {
        $tableName = static::UNDERLYING_CLASS::CATEGORY_TABLE;
        /** @noinspection SqlResolve */
        $this->connection->executeQuery("INSERT IGNORE INTO {$tableName}(id, categoryId, priority) VALUES (?, ?, ?)", [$object->id, $category->id, $priority]);
    }

    /**
     * @param T $object
     */
    public function unlinkFromCategory(ModelWithCategory $object, Category $category): void
    {
        $tableName = static::UNDERLYING_CLASS::CATEGORY_TABLE;
        /** @noinspection SqlResolve */
        $this->connection->executeQuery("DELETE FROM {$tableName} WHERE id = ? AND categoryId = ?", [$object->id, $category->id]);
    }

    /**
     * @param T $object
     * @throws \Exception
     * @return Category|null
     */
    public function getFirstLinkedCategory(ModelWithCategory $object): Category|null
    {
        $categories = $this->getLinkedCategories($object);
        if (empty($categories))
        {
            return null;
        }
        return reset($categories);
    }

    /**
     * @param T $object
     * @throws \Exception
     * @return Category[]
     */
    public function getLinkedCategories(ModelWithCategory $object): array
    {
        $tableName = static::UNDERLYING_CLASS::CATEGORY_TABLE;
        $categories = [];
        /** @noinspection SqlResolve */
        $entries = $this->connection->doQueryAndFetchAll("SELECT categoryId FROM {$tableName} WHERE id = ?", [$object->id]) ?: [];
        foreach ($entries as $entry)
        {
            /** @var int $id */
            $id = $entry['categoryId'];
            /** @var Category $category */
            $category = $this->genericRepository->fetchById(Category::class, $id);
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * @param T $object
     * @throws \Exception
     * @return int[]
     */
    public function getLinkedCategoryIds(ModelWithCategory $object): array
    {
        $categories = $this->getLinkedCategories($object);
        $ret = [];
        foreach ($categories as $category)
        {
            assert($category->id !== null);
            $ret[] = $category->id;
        }

        return $ret;
    }
}
