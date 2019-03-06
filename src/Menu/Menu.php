<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\DBConnection;
use Cyndaron\Model;

class Menu extends Model
{
    protected static $table = 'menu';

    public static function get()
    {
        $pdo = DBConnection::getPdo();
        $menu = $pdo->prepare('SELECT * FROM menu ORDER BY volgorde ASC;');
        $menu->execute();
        return $menu;
    }

    public static function deleteItem(int $index): void
    {
        DBConnection::doQuery('DELETE FROM menu WHERE volgorde=?', [$index]);
    }

    public static function replace(array $newMenu)
    {
        DBConnection::doQueryAndFetchOne('DELETE FROM menu;');

        if (count($newMenu) > 0)
        {
            $order = 1;
            foreach ($newMenu as $menuitem)
            {
                DBConnection::doQueryAndFetchOne('INSERT INTO menu(volgorde,link,alias) VALUES(?,?,?);', [$order, $menuitem['link'], $menuitem['alias']]);
                $order++;
            }
        }
    }

    public static function addItem(string $link, string $alias = '', int $order = null, bool $isDropdown = false, bool $isImage = false): bool
    {
        $order = $order ?: intval(DBConnection::doQueryAndFetchOne('SELECT MAX(volgorde) FROM menu;')) + 1;
        return (bool)DBConnection::doQuery('INSERT INTO menu(volgorde,link,alias,isDropdown,isImage) VALUES(?,?,?,?,?);',
            [$order, $link, $alias, $isDropdown, $isDropdown]);
    }

    public static function editItem(int $id, array $newArray): bool
    {
        $record = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM menu WHERE volgorde=?', [$id]);
        if (empty($record))
            throw new \Exception('Item bestaat niet!');
        $record = array_merge($record, $newArray);
        return (bool)DBConnection::doQuery('UPDATE menu SET volgorde=?, link=?, alias=?, isDropdown=?, isImage=? WHERE volgorde=?',
            [$record['volgorde'], $record['link'], $record['alias'], $record['isDropdown'], $record['isImage'], $id]);
    }
}