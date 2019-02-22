<?php
declare (strict_types = 1);

namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;

/**
 * Class FotoalbumModel
 * @package Cyndaron
 * @todo: Omvormen tot echt model.
 */
class PhotoalbumModel
{
    public static function nieuwFotoalbum($naam, $notities = "")
    {
        if ($naam == '')
            throw new \Exception('Empty photo album name!');

        $id = DBConnection::doQuery('INSERT INTO fotoboeken(`naam`,`notities`) VALUES (?,?);', [$naam, $notities]);
        if ($id !== false)
        {
            mkdir(__DIR__ . "/../../fotoalbums/${id}", 0777, true);
            mkdir(__DIR__ . "/../../fotoalbums/${id}thumbnails", 0777, true);
        }

        return $id;
    }

    public static function wijzigFotoalbum($id, $naam = null, $notities = null)
    {
        if ($naam !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE fotoboeken SET `naam`=? WHERE id=?', [$naam, $id]);
        }
        if ($notities !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE fotoboeken SET `notities`=? WHERE id=?', [$notities, $id]);
        }
    }

    public static function verwijderFotoalbum($id)
    {
        DBConnection::doQueryAndFetchOne('DELETE FROM fotoboeken WHERE id=?;', [$id]);
    }
}