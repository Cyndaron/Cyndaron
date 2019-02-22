<?php
namespace Cyndaron;

/**
 * Class FotoModel
 * @package Cyndaron
 * @todo: Omvormen tot echt model.
 */
class FotoModel
{
    public static function maakBijschrift($hash, $bijschrift)
    {
        DBConnection::doQueryAndFetchOne('DELETE FROM bijschriften WHERE hash = ?', [$hash]);
        DBConnection::doQueryAndFetchOne('INSERT INTO bijschriften(hash,bijschrift) VALUES (?,?)', [$hash, $bijschrift]);
    }
}