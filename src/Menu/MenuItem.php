<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\DBConnection;
use Cyndaron\Model;
use Cyndaron\Setting;
use Cyndaron\Url;

class MenuItem extends Model
{
    const TABLE = 'menu';
    const TABLE_FIELDS = ['link', 'alias', 'isDropdown', 'isImage', 'priority'];

    public string $link;
    public ?string $alias = null;
    public bool $isDropdown = false;
    public bool $isImage = false;
    public ?int $priority;

    public function save(): bool
    {
        if (!isset($this->priority) || $this->priority === '') {
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
        else
        {
            $url = new Url($this->link);
            return $url->getPageTitle();
        }
    }

    public function getLink(): string
    {
        if ($this->link == Setting::get('frontPage'))
        {
            return '/';
        }
        // For dropdowns, this is not necessary and it makes detection harder down the line.
        elseif (!$this->isDropdown)
        {
            $url = new Url($this->link);
            return $url->getFriendly();
        }

        return $this->link;
    }

    public function isCurrentPage(): bool
    {
        $link = $this->getLink();
        // Vergelijking na || betekent testen of de hoofdurl is opgevraagd
        if ($link == basename(substr($_SERVER['REQUEST_URI'], 1)) || ($link == '/' && $_SERVER['REQUEST_URI'] === '/'))
        {
            return true;
        }

        return false;
    }

    public function isCategoryDropdown(): bool
    {
        return strpos($this->link, '/category/') === 0 && $this->isDropdown;
    }

    public function getSubMenu()
    {
        $id = intval(str_replace('/category/', '', $this->link));
        $pagesInCategory = DBConnection::doQueryAndFetchAll("
            SELECT * FROM
            (
                SELECT 'sub' AS type, id, name FROM subs WHERE categoryId=?
                UNION
                SELECT 'photoalbum' AS type, id, name FROM photoalbums WHERE categoryId=?
                UNION
                SELECT 'category' AS type, id, name FROM categories WHERE categoryId=?
            ) AS one
            ORDER BY name ASC;",
            [$id, $id, $id]);

        $items = [];
        foreach ($pagesInCategory as $page)
        {
            $url = new Url(sprintf('/%s/%d', $page['type'], $page['id']));
            $link = $url->getFriendly();
            $items[] = ['link' => $link, 'title' => $page['name']];
        }
        return $items;
    }
}