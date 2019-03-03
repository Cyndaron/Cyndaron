<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    protected $hasTitle = true;
    protected $type = 'photoalbum';
    protected $table = 'fotoboeken';
    protected $saveUrl = '/editor/photoalbum/%s';

    protected function prepare()
    {
        if ($this->id)
        {
            $this->content = DBConnection::doQueryAndFetchOne('SELECT notities FROM fotoboeken WHERE id=?', [$this->id]);
            $this->contentTitle = DBConnection::doQueryAndFetchOne('SELECT naam FROM fotoboeken WHERE id=?', [$this->id]);
        }
    }

    // Not used, but required.
    protected function showContentSpecificButtons() {}
}