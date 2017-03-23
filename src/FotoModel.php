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
        DBConnection::geefEen('DELETE FROM bijschriften WHERE hash = ?', array($hash));
        DBConnection::geefEen('INSERT INTO bijschriften(hash,bijschrift) VALUES (?,?)', array($hash, $bijschrift));
    }
}