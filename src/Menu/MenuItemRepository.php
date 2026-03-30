<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\Category\CategoryRepository;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use Cyndaron\Url\UrlService;
use Cyndaron\Util\Link;
use function str_replace;
use function ltrim;

/**
 * @implements RepositoryInterface<MenuItem>
 */
final class MenuItemRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = MenuItem::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly UrlService $urlService
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
            $url = $this->urlService->getUrlForModel($page);
            $items[] = new Link((string)$url, $page->name);
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
