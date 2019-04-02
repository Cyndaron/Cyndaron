<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;

class EditorPagePhoto extends \Cyndaron\Editor\EditorPage
{
    const HAS_TTTLE = false;
    protected $type = 'photo';
    protected $table = 'bijschiften';
    protected $saveUrl = '/editor/photo/0/%s';

    protected function prepare()
    {
        if ($this->id)
        {
            $this->content = DBConnection::doQueryAndFetchOne('SELECT bijschrift FROM bijschriften WHERE hash=?', [$this->id]);
        }
    }

    // Not used, but required.
    protected function showContentSpecificButtons() {}
}
