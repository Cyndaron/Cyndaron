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
        DBConnection::doQueryAndFetchOne('DELETE FROM bijschriften WHERE hash = ?', [$hash]);
        DBConnection::doQueryAndFetchOne('INSERT INTO bijschriften(hash,bijschrift) VALUES (?,?)', [$hash, $caption]);
    }
}