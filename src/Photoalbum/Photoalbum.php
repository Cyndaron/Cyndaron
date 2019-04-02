<?php
declare (strict_types = 1);

namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;
use Cyndaron\Model;

class Photoalbum extends Model
{
    const HAS_CATEGORY = true;

    protected static $table = 'fotoboeken';

    public static function nieuwFotoalbum(string $naam, string $notities = "", bool $showBreadcrumbs = false)
    {
        if ($naam == '')
            throw new \Exception('Empty photo album name!');

        $id = DBConnection::doQuery('INSERT INTO fotoboeken(`naam`,`notities`) VALUES (?,?,?);', [$naam, $notities,(int)$showBreadcrumbs]);
        if ($id !== false)
        {
            mkdir(__DIR__ . "/../../fotoalbums/${id}", 0777, true);
            mkdir(__DIR__ . "/../../fotoalbums/${id}thumbnails", 0777, true);
        }

        return $id;
    }

    public static function wijzigFotoalbum(int $id, string $naam = null, $notities = null, bool $showBreadcrumbs = null)
    {
        if ($naam !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE fotoboeken SET `naam`=? WHERE id=?', [$naam, $id]);
        }
        if ($notities !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE fotoboeken SET `notities`=? WHERE id=?', [$notities, $id]);
        }
        if ($showBreadcrumbs !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE fotoboeken SET `showBreadcrumbs`=? WHERE id=?', [(int)$showBreadcrumbs, $id]);
        }
    }
}