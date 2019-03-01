<?php
namespace Cyndaron;

class EditorFoto extends EditorPage
{
    protected $hasTitle = false;
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

    protected function showContentSpecificButtons()
    {
        // Ongebruikt, maar verplicht.
    }
}
