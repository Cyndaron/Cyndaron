<?php
namespace Cyndaron;

class EditorStatischePagina extends EditorPagina
{

    protected function prepare()
    {
        $this->heeftTitel = true;
        $this->type = 'sub';
        $this->saveUrl = 'bewerk-statischepagina?actie=bewerken&amp;id=%s';

        if ($this->id)
        {
            $this->content = DBConnection::geefEen('SELECT tekst FROM ' . $this->vvstring . 'subs WHERE id=?', [$this->id]);
            $this->titel = DBConnection::geefEen('SELECT naam FROM ' . $this->vvstring . 'subs WHERE id=?', [$this->id]);
        }

    }

    protected function toonSpecifiekeKnoppen()
    {
        $checked = DBConnection::geefEen('SELECT reacties_aan FROM subs WHERE id=?', [$this->id]) ? ' checked="checked"' : '';
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="reacties_aan">Reacties aan: </label>
            <div class="col-sm-5">
                <input id="reacties_aan" name="reacties_aan" type="checkbox" <?= $checked; ?>/>
            </div>
        </div>
        <?php
        $this->showCategoryDropdown();
    }
}