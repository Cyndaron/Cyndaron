<?php
namespace Cyndaron\Category;

use Cyndaron\DBConnection;
use Cyndaron\Util;

/**
 * Class CategoryModel
 * @todo: Omvormen tot echt model.
 */
class CategoryModel
{
    public static function nieuweCategorie($naam, bool $alleentitel = false, string $beschrijving = '', $categorieId = null)
    {
        if ($naam == '')
            throw new \Exception('Empty category name!');

        return DBConnection::maakEen('INSERT INTO categorieen(`naam`,`alleentitel`, `beschrijving`, `categorieid`) VALUES (?,?,?,?);', [$naam, (int)$alleentitel, $beschrijving, $categorieId]);
    }

    public static function wijzigCategorie($id, $naam = null, $alleentitel = null, $beschrijving = null, $categorieId = null)
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
        DBConnection::geefEen('UPDATE categorieen SET `categorieid`=? WHERE id=?', [$categorieId, $id]);
    }

    public static function verwijderCategorie($id)
    {
        DBConnection::geefEen('DELETE FROM categorieen WHERE id=?;', [$id]);
    }
}