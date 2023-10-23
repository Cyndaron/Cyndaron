<?php
namespace Cyndaron\Category;

use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\View\Template\ViewHelpers;
use Safe\Exceptions\PcreException;
use function Safe\preg_match;
use function html_entity_decode;
use function trim;
use function reset;
use function assert;

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
        return $this->previewImage ?: $this->getImage() ?: $this->getImageFromText();
    }

    /**
     * Fetches the first image from the page body.
     * Most useful as a fallback.
     *
     * @return string
     */
    public function getImageFromText(): string
    {
        try
        {
            preg_match('/<img.*?src="(.*?)".*?>/si', $this->getText(), $match);
            return $match[1] ?? '';
        }
        catch (PcreException $e)
        {
            return '';
        }
    }

    /**
     * @throws \Exception
     * @return Category|null
     */
    public function getFirstCategory(): Category|null
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
        /** @noinspection SqlResolve */
        $entries = DBConnection::getPDO()->doQueryAndFetchAll("SELECT categoryId FROM {$tableName} WHERE id = ?", [$this->id]) ?: [];
        foreach ($entries as $entry)
        {
            /** @var int $id */
            $id = $entry['categoryId'];
            /** @var Category $category */
            $category = Category::fetchById($id);
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * @throws \Exception
     * @return int[]
     */
    public function getCategoryIds(): array
    {
        $categories = $this->getCategories();
        $ret = [];
        foreach ($categories as $category)
        {
            assert($category->id !== null);
            $ret[] = $category->id;
        }

        return $ret;
    }

    public function addCategory(Category $category): void
    {
        $tableName = static::CATEGORY_TABLE;
        /** @noinspection SqlResolve */
        DBConnection::getPDO()->executeQuery("INSERT IGNORE INTO {$tableName}(id, categoryId) VALUES (?, ?)", [$this->id, $category->id]);
    }

    public function removeCategory(Category $category): void
    {
        $tableName = static::CATEGORY_TABLE;
        /** @noinspection SqlResolve */
        DBConnection::getPDO()->executeQuery("DELETE FROM {$tableName} WHERE id = ? AND categoryId = ?", [$this->id, $category->id]);
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
        /** @noinspection SqlResolve */
        $entries = DBConnection::getPDO()->doQueryAndFetchAll("SELECT DISTINCT id, priority FROM {$tableName} WHERE categoryId = ? " . $afterWhere, [$category->id]) ?: [];
        $ret = [];
        foreach ($entries as $entry)
        {
            /** @var int $id */
            $id = $entry['id'];
            $model = static::fetchById($id);
            assert($model !== null);
            $model->priority = (int)$entry['priority'];
            $ret[] = $model;
        }

        return $ret;
    }
}
