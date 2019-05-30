<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;

class EditorPagePhoto extends \Cyndaron\Editor\EditorPage
{
    const HAS_TITLE = false;
    const TYPE = 'photo';
    const TABLE = 'bijschiften';
    const SAVE_URL = '/editor/photo/0/%s';

    protected function prepare()
    {
        if ($this->id)
        {
            $this->content = DBConnection::doQueryAndFetchOne('SELECT caption FROM photoalbum_captions WHERE hash=?', [$this->id]);
        }
    }

    // Not used, but required.
    protected function showContentSpecificButtons() {}
}
