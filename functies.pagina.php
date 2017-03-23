<?php

use Cyndaron\DBConnection;
use Cyndaron\Gebruiker;
/**
 * Functies voor het beheren van pagina's en elementen erop.
 */
function nieuweCategorie($naam, $alleentitel = false, $beschrijving = '')
{
    return DBConnection::maakEen('INSERT INTO categorieen(`naam`,`alleentitel`, `beschrijving`) VALUES (?,?,?);', array($naam, (int)$alleentitel, $beschrijving));
}

function nieuwFotoalbum($naam, $notities = "")
{
    return DBConnection::maakEen('INSERT INTO fotoboeken(`naam`,`notities`) VALUES (?,?);', array($naam, $notities));
}

function wijzigCategorie($id, $naam = null, $alleentitel = null, $beschrijving = null)
{
    if ($naam !== null)
        DBConnection::geefEen('UPDATE categorieen SET `naam`=? WHERE id=?', array($naam, $id));
    if ($alleentitel !== null)
        DBConnection::geefEen('UPDATE categorieen SET `alleentitel`=? WHERE id=?', array(parseCheckboxAlsInt($alleentitel), $id));
    if ($beschrijving !== null)
        DBConnection::geefEen('UPDATE categorieen SET `beschrijving`=? WHERE id=?', array($beschrijving, $id));
}

function wijzigFotoalbum($id, $naam = null, $notities = null)
{
    if ($naam !== null)
        DBConnection::geefEen('UPDATE fotoboeken SET `naam`=? WHERE id=?', array($naam, $id));
    if ($notities !== null)
        DBConnection::geefEen('UPDATE fotoboeken SET `notities`=? WHERE id=?', array($notities, $id));
}

function verwijderCategorie($id)
{
    DBConnection::geefEen('DELETE FROM categorieen WHERE id=?;', array($id));
}

function verwijderFotoalbum($id)
{
    DBConnection::geefEen('DELETE FROM fotoboeken WHERE id=?;', array($id));
}

function maakBijschrift($hash, $bijschrift)
{
    DBConnection::geefEen('DELETE FROM bijschriften WHERE hash = ?', array($hash));
    DBConnection::geefEen('INSERT INTO bijschriften(hash,bijschrift) VALUES (?,?)', array($hash, $bijschrift));
}

function parseCheckboxAlsInt($waarde)
{
    if (!$waarde)
        return 0;
    else
        return 1;
}

function parseCheckBoxAlsBool($waarde)
{
    if (!$waarde)
        return false;
    else
        return true;
}



function toonIndienAanwezig($string, $voor = null, $na = null)
{
    if ($string)
    {
        echo $voor;
        echo $string;
        echo $na;
    }
}

function vervangMenu($nieuwmenu)
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

function voegToeAanMenu($link, $alias = "")
{
    $teller = DBConnection::geefEen('SELECT MAX(volgorde) FROM menu;', array()) + 1;
    DBConnection::geefEen('INSERT INTO menu(volgorde,link,alias) VALUES(?,?,?);', array($teller, $link, $alias));
}

function toonIndienAanwezigEnAdmin($string, $voor = null, $na = null)
{
    if (Gebruiker::isAdmin() && $string)
    {
        echo $voor;
        echo $string;
        echo $na;
    }
}

function toonIndienAanwezigEnGeenAdmin($string, $voor = null, $na = null)
{
    if (!Gebruiker::isAdmin() && $string)
    {
        echo $voor;
        echo $string;
        echo $na;
    }
}