<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;

/**
 * @todo: Turn into a real model.
 */
class PhotoModel
{
    public static function addCaption(string $hash, string $caption)
    {
        DBConnection::doQueryAndFetchOne('DELETE FROM photoalbum_captions WHERE hash = ?', [$hash]);
        DBConnection::doQueryAndFetchOne('INSERT INTO photoalbum_captions(hash,caption) VALUES (?,?)', [$hash, $caption]);
    }
}