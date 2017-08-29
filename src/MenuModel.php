<?php
namespace Cyndaron;

class MenuModel
{
    public static function vervangMenu($nieuwmenu)
    {
        DBConnection::geefEen('DELETE FROM menu;', []);

        if (count($nieuwmenu) > 0)
        {
            $teller = 1;
            foreach ($nieuwmenu as $menuitem)
            {
                DBConnection::geefEen('INSERT INTO menu(volgorde,link,alias) VALUES(?,?,?);', [$teller, $menuitem['link'], $menuitem['alias']]);
                $teller++;
            }
        }
    }

    public static function voegToeAanMenu($link, $alias = "")
    {
        $teller = intval(DBConnection::geefEen('SELECT MAX(volgorde) FROM menu;', [])) + 1;
        DBConnection::geefEen('INSERT INTO menu(volgorde,link,alias) VALUES(?,?,?);', [$teller, $link, $alias]);
    }
}