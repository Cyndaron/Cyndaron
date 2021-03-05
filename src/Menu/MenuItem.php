<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Model;
use Cyndaron\Setting;
use Cyndaron\Url;

use function Safe\sprintf;
use function is_string;
use function ltrim;
use function strpos;
use function str_replace;

final class MenuItem extends Model
{
    public const TABLE = 'menu';
    public const TABLE_FIELDS = ['link', 'alias', 'isDropdown', 'isImage', 'priority'];

    public string $link;
    public ?string $alias = null;
    public bool $isDropdown = false;
    public bool $isImage = false;
    public ?int $priority = null;

    public function save(): bool
    {
        if (!$this->priority)
        {
            $priority = DBConnection::doQueryAndFetchOne('SELECT MAX(priority) FROM menu WHERE id <> ?', [$this->id]) + 1;
            $this->priority = $priority;
        }
        return parent::save();
    }

    public function getTitle(): string
    {
        if (is_string($this->alias) && !empty($this->alias))
        {
            return $this->alias;
        }

        $url = new Url($this->link);
        return $url->getPageTitle();
    }

    public function getLink(): string
    {
        if ($this->link === Setting::get('frontPage'))
        {
            return '/';
        }

        $link = $this->link;
        // For dropdowns, this is not necessary and it makes detection harder down the line.
        if (!$this->isDropdown)
        {
            $url = new Url($this->link);
            $link = $url->getFriendly();
        }

        // Do not put a slash in front of URLs that already include the protocol.
        if (strpos($link, ':/'))
        {
            return $link;
        }

        // For relative URLs, ensure there is a slash in front.
        return '/' . ltrim($link, '/');
    }

    public function isCurrentPage(): bool
    {
        $link = $this->getLink();
        // The first comparison checks if the homepage has been requested.
        if (($link === '/' && $_SERVER['REQUEST_URI'] === '/') || $link === $_SERVER['REQUEST_URI'])
        {
            return true;
        }

        return false;
    }

    public function isCategoryDropdown(): bool
    {
        return strpos($this->link, '/category/') === 0 && $this->isDropdown;
    }

    public function getSubmenu(): array
    {
        // TODO: handle this via the module system
        $id = (int)str_replace('/category/', '', $this->link);
        $pagesInCategory = DBConnection::doQueryAndFetchAll(
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
            $urlString = $page['url'] ?: sprintf('/%s/%d', $page['type'], $page['id']);
            $url = $url = new Url($urlString);
            $link = $url->getFriendly();
            $items[] = ['link' => $link, 'title' => $page['name']];
        }
        return $items;
    }
}
