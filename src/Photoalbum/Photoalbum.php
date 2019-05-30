<?php
declare (strict_types = 1);

namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;
use Cyndaron\Model;
use Exception;

class Photoalbum extends Model
{
    const TABLE = 'photoalbums';
    const TABLE_FIELDS = ['name', 'notes', 'categoryId', 'showBreadcrumbs'];
    const HAS_CATEGORY = true;

    public $name = '';
    public $notes = '';
    public $categoryId = null;
    public $showBreadcrumbs = false;

    public static function create(string $naam, string $notities = "", bool $showBreadcrumbs = false)
    {
        if ($naam == '')
            throw new Exception('Empty photo album name!');

        $id = DBConnection::doQuery('INSERT INTO photoalbums(`name`,`notes`,`showBreadcrumbs`) VALUES (?,?,?);', [$naam, $notities,(int)$showBreadcrumbs]);
        if ($id !== false)
        {
            mkdir(__DIR__ . "/../../fotoalbums/${id}", 0777, true);
            mkdir(__DIR__ . "/../../fotoalbums/${id}thumbnails", 0777, true);
        }

        return $id;
    }

    public static function edit(int $id, string $naam = null, $notities = null, bool $showBreadcrumbs = null)
    {
        if ($naam !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE photoalbums SET `name`=? WHERE id=?', [$naam, $id]);
        }
        if ($notities !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE photoalbums SET `notes`=? WHERE id=?', [$notities, $id]);
        }
        if ($showBreadcrumbs !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE photoalbums SET `showBreadcrumbs`=? WHERE id=?', [(int)$showBreadcrumbs, $id]);
        }
    }
}