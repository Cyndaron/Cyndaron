<?php
namespace Cyndaron;

class EditorFotoalbum extends EditorPagina
{
    protected function prepare()
    {
        $this->heeftTitel = true;
        $this->type = 'fotoboek';
        $this->table = 'fotoboeken';
        $this->saveUrl = 'bewerk-fotoalbum?actie=bewerken&amp;id=%s';

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