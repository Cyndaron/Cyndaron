<?php
namespace Cyndaron;

class EditorFoto extends EditorPage
{
    protected $heeftTitel = false;
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

    protected function toonSpecifiekeKnoppen()
    {
        // Ongebruikt, maar verplicht.
    }
}
