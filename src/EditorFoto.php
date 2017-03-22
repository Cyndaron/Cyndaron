<?php
namespace Cyndaron;

class EditorFoto extends EditorPagina
{
    protected function prepare()
    {
        $this->heeftTitel = false;
        $this->type = 'foto';

        if ($this->id)
        {
            $this->content = geefEen('SELECT bijschrift FROM bijschriften WHERE hash=?', array($this->id));
        }
    }

    protected function toonSpecifiekeKnoppen()
    {
        // Ongebruikt, maar verplicht.
    }
}
