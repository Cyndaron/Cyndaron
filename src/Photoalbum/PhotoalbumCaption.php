<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;
use Cyndaron\Model;

class PhotoalbumCaption extends Model
{
    const TABLE = 'photoalbum_captions';
    const TABLE_FIELDS = ['hash', 'caption'];

    public $hash;
    public $caption;

    public static function create(string $hash, string $caption)
    {
        DBConnection::doQueryAndFetchOne('DELETE FROM photoalbum_captions WHERE hash = ?', [$hash]);
        DBConnection::doQueryAndFetchOne('INSERT INTO photoalbum_captions(hash,caption) VALUES (?,?)', [$hash, $caption]);
    }

    public static function loadByHash(string $hash): ?PhotoalbumCaption
    {
        $obj = null;
        $result = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM photoalbum_captions WHERE hash=?', [$hash]);
        if ($result !== null && $result !== false) {
            $obj = new static();
            $obj->id = $result['id'];
            $obj->updateFromArray($result);
        }
        return $obj;
    }
}