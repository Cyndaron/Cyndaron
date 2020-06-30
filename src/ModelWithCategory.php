<?php
namespace Cyndaron;

use Cyndaron\Category\Category;
use Cyndaron\Template\ViewHelpers;

/**
 * Class ModelWithCategory
 *
 * @property string $type May be present as a helper for the category settings
 */
abstract class ModelWithCategory extends Model
{
    public string $name = '';
    public string $image = '';
    public string $previewImage = '';
    public string $blurb = '';
    public bool $showBreadcrumbs = false;
    public bool $openInNewTab = false;

    // Saved in coupling table!
    public int $priority = 0;

    public const CATEGORY_TABLE = '';

    abstract public function getFriendlyUrl(): string;

    public function getBlurb(): string
    {
        $text = $this->blurb ?: $this->getText();
        return html_entity_decode(ViewHelpers::wordlimit(trim($text), 30));
    }

    abstract public function getText(): string;

    public function getImage(): string
    {
        return $this->image;
    }

    public function getPreviewImage(): string
    {
        return $this->previewImage ?: $this->getImage();
    }

    /**
     * @throws \Exception
     * @return Category|null
     */
    public function getFirstCategory(): ?Category
    {
        $categories = $this->getCategories();
        if (empty($categories))
        {
            return null;
        }
        return reset($categories);
    }

    /**
     * @throws \Exception
     * @return Category[]
     */
    public function getCategories(): array
    {
        $tableName = static::CATEGORY_TABLE;
        $categories = [];
        $entries = DBConnection::doQueryAndFetchAll("SELECT categoryId FROM {$tableName} WHERE id = ?", [$this->id]);
        foreach ($entries as $entry)
        {
            $categories[] = Category::loadFromDatabase($entry['categoryId']);
        }

        return $categories;
    }

    /**
     * @return int[]
     */
    public function getCategoryIds(): array
    {
        $categories = $this->getCategories();
        $ret = [];
        foreach ($categories as $category)
        {
            $ret[] = $category->id;
        }

        return $ret;
    }

    public function addCategory(Category $category)
    {
        $tableName = static::CATEGORY_TABLE;
        DBConnection::doQuery("INSERT IGNORE INTO {$tableName}(id, categoryId) VALUES (?, ?)", [$this->id, $category->id]);
    }

    public function removeCategory(Category $category)
    {
        $tableName = static::CATEGORY_TABLE;
        DBConnection::doQuery("DELETE FROM {$tableName} WHERE id = ? AND categoryId = ?", [$this->id, $category->id]);
    }

    /**
     * @param Category $category
     * @param string $afterWhere
     * @throws \Exception
     * @return static[]
     */
    public static function fetchAllByCategory(Category $category, string $afterWhere = ''): array
    {
        $tableName = static::CATEGORY_TABLE;
        $entries = DBConnection::doQueryAndFetchAll("SELECT DISTINCT id, priority FROM {$tableName} WHERE categoryId = ? " . $afterWhere, [$category->id]);
        $ret = [];
        foreach ($entries as $entry)
        {
            $model = static::loadFromDatabase($entry['id']);
            $model->priority = $entry['priority'];
            $ret[] = $model;
        }

        return $ret;
    }
}
