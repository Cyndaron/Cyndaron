<?php
namespace Cyndaron;

/**
 * Class CategorieModel
 * @package Cyndaron
 * @todo: Omvormen tot echt model.
 */
class CategorieModel
{
    public static function nieuweCategorie($naam, $alleentitel = false, $beschrijving = '')
    {
        return DBConnection::maakEen('INSERT INTO categorieen(`naam`,`alleentitel`, `beschrijving`) VALUES (?,?,?);', [$naam, (int)$alleentitel, $beschrijving]);
    }

    public static function wijzigCategorie($id, $naam = null, $alleentitel = null, $beschrijving = null)
    {
        if ($naam !== null)
        {
            DBConnection::geefEen('UPDATE categorieen SET `naam`=? WHERE id=?', [$naam, $id]);
        }
        if ($alleentitel !== null)
        {
            DBConnection::geefEen('UPDATE categorieen SET `alleentitel`=? WHERE id=?', [Util::parseCheckboxAlsInt($alleentitel), $id]);
        }
        if ($beschrijving !== null)
        {
            DBConnection::geefEen('UPDATE categorieen SET `beschrijving`=? WHERE id=?', [$beschrijving, $id]);
        }
    }

    public static function verwijderCategorie($id)
    {
        DBConnection::geefEen('DELETE FROM categorieen WHERE id=?;', [$id]);
    }
}