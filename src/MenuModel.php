<?php
namespace Cyndaron;

class MenuModel
{
    public static function vervangMenu($nieuwmenu)
    {
        DBConnection::geefEen('DELETE FROM menu;', array());

        if (count($nieuwmenu) > 0)
        {
            $teller = 1;
            foreach ($nieuwmenu as $menuitem)
            {
                DBConnection::geefEen('INSERT INTO menu(volgorde,link,alias) VALUES(?,?,?);', array($teller, $menuitem['link'], $menuitem['alias']));
                $teller++;
            }
        }
    }

    public static function voegToeAanMenu($link, $alias = "")
    {
        $teller = DBConnection::geefEen('SELECT MAX(volgorde) FROM menu;', array()) + 1;
        DBConnection::geefEen('INSERT INTO menu(volgorde,link,alias) VALUES(?,?,?);', array($teller, $link, $alias));
    }
}