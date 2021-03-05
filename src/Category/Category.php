<?php
namespace Cyndaron\Category;

use Cyndaron\Category\ModelWithCategory;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\RichLink\RichLink;
use Cyndaron\StaticPage\StaticPageModel;
use Cyndaron\Url;

use function Safe\strtotime;
use function Safe\usort;
use function array_merge;
use function strcasecmp;

final class Category extends ModelWithCategory
{
    public const TABLE = 'categories';
    public const CATEGORY_TABLE = 'category_categories';
    public const TABLE_FIELDS = ['name', 'image', 'previewImage', 'blurb', 'description', 'viewMode', 'showBreadcrumbs'];

    public const VIEWMODE_REGULAR = 0;
    public const VIEWMODE_TITLES = 1;
    public const VIEWMODE_BLOG = 2;
    public const VIEWMODE_PORTFOLIO = 3;
    public const VIEWMODE_HORIZONTAL = 4;

    public const VIEWMODE_DESCRIPTIONS = [
        self::VIEWMODE_REGULAR => 'Samenvatting',
        self::VIEWMODE_TITLES => 'Alleen titels',
        self::VIEWMODE_BLOG => 'Blog',
        self::VIEWMODE_PORTFOLIO => 'Portfolio',
        self::VIEWMODE_HORIZONTAL => 'Horizontaal',
    ];

    public string $description = '';
    public int $viewMode = self::VIEWMODE_REGULAR;

    public function getFriendlyUrl(): string
    {
        $url = new Url('/category/' . $this->id);
        return $url->getFriendly();
    }

    public function getText(): string
    {
        return $this->description;
    }

    /**
     * @param string $orderBy
     * @throws \Exception
     * @return ModelWithCategory[]
     */
    public function getUnderlyingPages(string $orderBy = ''): array
    {
        $ret = array_merge(
            StaticPageModel::fetchAllByCategory($this),
            self::fetchAllByCategory($this),
            Photoalbum::fetchAllByCategory($this),
            RichLink::fetchAllByCategory($this),
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
                return strtotime($m2->created) <=> strtotime($m1->created);
            });
        }

        return $ret;
    }
}
