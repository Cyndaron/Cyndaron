<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\DBConnection;

class MenuModel
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
                DBConnection::doQuery('UPDATE menu SET isDropdown=? WHERE volgorde=?', [$value, $index]);
                return true;
            case 'isImage':
                DBConnection::doQuery('UPDATE menu SET isImage=? WHERE volgorde=?', [$value, $index]);
                return true;
            default:
                return false;

        }
    }

    public static function vervangMenu(array $nieuwmenu)
    {
        DBConnection::doQueryAndFetchOne('DELETE FROM menu;');

        if (count($nieuwmenu) > 0)
        {
            $teller = 1;
            foreach ($nieuwmenu as $menuitem)
            {
                DBConnection::doQueryAndFetchOne('INSERT INTO menu(volgorde,link,alias) VALUES(?,?,?);', [$teller, $menuitem['link'], $menuitem['alias']]);
                $teller++;
            }
        }
    }

    public static function voegToeAanMenu(string $link, string $alias = '')
    {
        $teller = intval(DBConnection::doQueryAndFetchOne('SELECT MAX(volgorde) FROM menu;')) + 1;
        DBConnection::doQueryAndFetchOne('INSERT INTO menu(volgorde,link,alias) VALUES(?,?,?);', [$teller, $link, $alias]);
    }
}