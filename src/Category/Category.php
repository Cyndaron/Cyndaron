<?php
namespace Cyndaron\Category;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\RichLink\RichLink;
use Cyndaron\StaticPage\StaticPageModel;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;
use function array_merge;
use function strcasecmp;
use function usort;

final class Category extends ModelWithCategory
{
    public const TABLE = 'categories';
    public const CATEGORY_TABLE = 'category_categories';

    #[DatabaseField]
    public string $description = '';
    #[DatabaseField]
    public ViewMode $viewMode = ViewMode::Regular;

    public function getFriendlyUrl(UrlService $urlService): Url
    {
        $url = new Url('/category/' . $this->id);
        return $urlService->toFriendly($url);
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
                return $m2->created <=> $m1->created;
            });
        }

        return $ret;
    }
}
