<?php
namespace Cyndaron;

class EditorFotoalbum extends EditorPage
{
    protected function prepare()
    {
        $this->heeftTitel = true;
        $this->type = 'photoalbum';
        $this->table = 'fotoboeken';
        $this->saveUrl = '/editor/photoalbum/%s';

        if ($this->id)
        {
            $this->content = DBConnection::geefEen('SELECT notities FROM fotoboeken WHERE id=?', [$this->id]);
            $this->titel = DBConnection::geefEen('SELECT naam FROM fotoboeken WHERE id=?', [$this->id]);
        }
    }

    protected function toonSpecifiekeKnoppen()
    {
        // Ongebruikt, maar verplicht.
    }
}