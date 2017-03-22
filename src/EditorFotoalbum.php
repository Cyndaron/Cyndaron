<?php
namespace Cyndaron;

class EditorFotoalbum extends EditorPagina
{
    protected function prepare()
    {
        $this->heeftTitel = true;
        $this->type = 'fotoboek';
        $this->saveUrl = 'bewerk-fotoalbum?actie=bewerken&amp;id=%s';

        if ($this->id)
        {
            $this->content = geefEen('SELECT notities FROM fotoboeken WHERE id=?', array($this->id));
            $this->titel = geefEen('SELECT naam FROM fotoboeken WHERE id=?', array($this->id));
        }
    }

    protected function toonSpecifiekeKnoppen()
    {
        // Ongebruikt, maar verplicht.
    }
}