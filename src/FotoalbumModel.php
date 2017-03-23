<?php
namespace Cyndaron;

/**
 * Class FotoalbumModel
 * @package Cyndaron
 * @todo: Omvormen tot echt model.
 */
class FotoalbumModel
{
    public static function nieuwFotoalbum($naam, $notities = "")
    {
        return DBConnection::maakEen('INSERT INTO fotoboeken(`naam`,`notities`) VALUES (?,?);', array($naam, $notities));
    }

    public static function wijzigFotoalbum($id, $naam = null, $notities = null)
    {
        if ($naam !== null)
            DBConnection::geefEen('UPDATE fotoboeken SET `naam`=? WHERE id=?', array($naam, $id));
        if ($notities !== null)
            DBConnection::geefEen('UPDATE fotoboeken SET `notities`=? WHERE id=?', array($notities, $id));
    }

    public static function verwijderFotoalbum($id)
    {
        DBConnection::geefEen('DELETE FROM fotoboeken WHERE id=?;', array($id));
    }
}