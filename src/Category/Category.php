<?php
namespace Cyndaron\Category;

use Cyndaron\DBConnection;
use Cyndaron\Model;
use Cyndaron\Util;

class Category extends Model
{
    const HAS_CATEGORY = true;

    protected static $table = 'categorieen';

    public static function create($naam, bool $alleentitel = false, string $beschrijving = '', int $categorieId = null, bool $showBreadcrumbs = false)
    {
        if ($naam == '')
            throw new \Exception('Empty category name!');

        return DBConnection::doQuery('INSERT INTO categorieen(`naam`,`alleentitel`, `beschrijving`, `categorieid`, `showBreadcrumbs`) VALUES (?,?,?,?,?);', [$naam, (int)$alleentitel, $beschrijving, $categorieId, (int)$showBreadcrumbs]);
    }

    public static function edit($id, $naam = null, bool $alleentitel = null, $beschrijving = null, $categorieId = null, bool $showBreadcrumbs = null)
    {
        if ($naam !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE categorieen SET `naam`=? WHERE id=?', [$naam, $id]);
        }
        if ($alleentitel !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE categorieen SET `alleentitel`=? WHERE id=?', [(int)$alleentitel, $id]);
        }
        if ($beschrijving !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE categorieen SET `beschrijving`=? WHERE id=?', [$beschrijving, $id]);
        }
        DBConnection::doQueryAndFetchOne('UPDATE categorieen SET `categorieid`=? WHERE id=?', [$categorieId, $id]);
        DBConnection::doQueryAndFetchOne('UPDATE categorieen SET `showBreadcrumbs`=? WHERE id=?', [(int)$showBreadcrumbs, $id]);
    }
}