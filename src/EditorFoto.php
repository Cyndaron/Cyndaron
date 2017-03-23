<?php
namespace Cyndaron;

class EditorFoto extends EditorPagina
{
    protected function prepare()
    {
        $this->heeftTitel = false;
        $this->type = 'foto';
        $this->saveUrl = 'bewerk-foto?actie=bewerken&amp;id=0&amp;hash=%s';

        if ($this->id)
        {
            $this->content = DBConnection::geefEen('SELECT bijschrift FROM bijschriften WHERE hash=?', array($this->id));
        }
    }

    protected function toonSpecifiekeKnoppen()
    {
        // Ongebruikt, maar verplicht.
    }
}
