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

}