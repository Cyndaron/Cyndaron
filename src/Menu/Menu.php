<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\DBConnection;

class Menu
{
    public static function get()
    {
        $pdo = DBConnection::getPdo();
        $menu = $pdo->prepare('SELECT * FROM menu ORDER BY volgorde ASC;');
        $menu->execute();
        return $menu;
    }

    public static function removeItem(int $index): void
    {
        DBConnection::doQuery('DELETE FROM menu WHERE volgorde=?', [$index]);
    }

    public static function setProperty(int $index, string $property, $value): bool
    {
        switch ($property)
        {
            case 'isDropdown':
                return (bool)DBConnection::doQuery('UPDATE menu SET isDropdown=? WHERE volgorde=?', [$value, $index]);
            case 'isImage':
                return (bool)DBConnection::doQuery('UPDATE menu SET isImage=? WHERE volgorde=?', [$value, $index]);
            default:
                return false;

        }
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

    public static function addItem(string $link, string $alias = '')
    {
        $order = intval(DBConnection::doQueryAndFetchOne('SELECT MAX(volgorde) FROM menu;')) + 1;
        DBConnection::doQueryAndFetchOne('INSERT INTO menu(volgorde,link,alias) VALUES(?,?,?);', [$order, $link, $alias]);
    }
}