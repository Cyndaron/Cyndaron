<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use Cyndaron\Util\Link;
use function sprintf;
use function str_replace;

/**
 * @implements RepositoryInterface<MenuItem>
 */
final class MenuItemRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = MenuItem::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
        private readonly Connection $connection,
    ) {
    }

    /**
     * @return Link[]
     */
    public function getSubmenu(MenuItem $menuItem): array
    {
        // TODO: handle this via the module system
        $id = (int)str_replace('/category/', '', $menuItem->link);
        $pagesInCategory = $this->connection->doQueryAndFetchAll(
            "
            SELECT * FROM
            (
                SELECT 'sub' AS type, id, name, '' AS url FROM subs WHERE id IN (SELECT id FROM sub_categories WHERE categoryId = ?)
                UNION
                SELECT 'photoalbum' AS type, id, name, '' AS url FROM photoalbums WHERE id IN (SELECT id FROM photoalbum_categories WHERE categoryId = ?)
                UNION
                SELECT 'category' AS type, id, name, '' AS url FROM categories WHERE id IN (SELECT id FROM category_categories WHERE categoryId = ?)
                UNION
                SELECT 'richlink' AS type, id, name, url FROM richlink WHERE id IN (SELECT id FROM richlink_category WHERE categoryId = ?)
            ) AS one
            ORDER BY name ASC;",
            [$id, $id, $id, $id]
        ) ?: [];

        $items = [];
        foreach ($pagesInCategory as $page)
        {
            $urlString = (string)$page['url'] ?: sprintf('/%s/%d', $page['type'], $page['id']);
            $items[] = new Link($urlString, (string)$page['name']);
        }
        return $items;
    }
}
