<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\Category\Category;
use Cyndaron\Category\CategoryRepository;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\RichLink\RichLink;
use Cyndaron\StaticPage\StaticPage;
use Cyndaron\Util\Link;
use function sprintf;
use function str_replace;
use function get_class;
use function ltrim;

/**
 * @implements RepositoryInterface<MenuItem>
 */
final class MenuItemRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = MenuItem::class;

    use RepositoryTrait;

    private const URL_MAPPING = [
        StaticPage::class => 'sub',
        Category::class => 'category',
        Photoalbum::class => 'photoalbum',
        RichLink::class => 'richlink',
    ];

    public function __construct(
        private readonly GenericRepository $genericRepository,
        private readonly CategoryRepository $categoryRepository
    ) {
    }

    /**
     * @return Link[]
     */
    public function getSubmenu(MenuItem $menuItem): array
    {
        // TODO: handle this via the module system
        $id = (int)str_replace('/category/', '', $menuItem->link);
        $category = $this->categoryRepository->fetchById($id);
        if ($category === null)
        {
            return [];
        }

        $pagesInCategory = $this->categoryRepository->getUnderlyingPages($category, 'name');

        $items = [];
        foreach ($pagesInCategory as $page)
        {
            if ($page instanceof RichLink)
            {
                $url = $page->url;
            }
            else
            {
                $type = self::URL_MAPPING[get_class($page)] ?? '';
                $url = sprintf('/%s/%d', $type, $page->id);
            }

            $items[] = new Link($url, $page->name);
        }
        return $items;
    }

    /**
     * @param string $link
     * @return MenuItem[]
     */
    public function fetchByLink(string $link): array
    {
        $trimmedLink = ltrim($link, '/');
        return $this->fetchAll(['link = ? OR link = ?'], [$link, $trimmedLink]);
    }
}
