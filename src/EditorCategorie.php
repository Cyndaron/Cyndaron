<?php
namespace Cyndaron;

class EditorCategorie extends EditorPagina
{
    protected function prepare()
    {
        $this->heeftTitel = true;
        $this->type = 'categorie';

        if ($this->id)
        {
            $this->content = geefEen('SELECT beschrijving FROM categorieen WHERE id=?', array($this->id));
            $this->titel = geefEen('SELECT naam FROM categorieen WHERE id=?', array($this->id));
        }
    }

    protected function toonSpecifiekeKnoppen()
    {
        $checked = geefEen('SELECT alleentitel FROM categorieen WHERE id=?', array($this->id)) ? 'checked="checked"' : '';
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="alleentitel">Toon alleen titels: </label>
            <div class="col-sm-5">
                <input id="alleentitel" name="alleentitel" type="checkbox" <?=$checked;?>/>
            </div>
        </div>
        <?php
    }
}