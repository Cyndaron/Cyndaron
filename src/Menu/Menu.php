<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\DBConnection;

class Menu
{
    public static function get()
    {
        $pdo = DBConnection::getPdo();
        $menu = $pdo->prepare('SELECT * FROM menu ORDER BY id ASC;');
        $menu->execute();
        return $menu;
    }

    public static function replace(array $newMenu)
    {
        DBConnection::doQueryAndFetchOne('DELETE FROM menu;');

        if (count($newMenu) > 0)
        {
            $order = 1;
            foreach ($newMenu as $menuitem)
            {
                DBConnection::doQueryAndFetchOne('INSERT INTO menu(id,link,alias) VALUES(?,?,?);', [$order, $menuitem['link'], $menuitem['alias']]);
                $order++;
            }
        }
    }
}