<?php
namespace Cyndaron;

class EditorStatischePagina extends EditorPagina
{

    protected function prepare()
    {
        $this->heeftTitel = true;
        $this->type = 'sub';

        if ($this->id)
        {
            $this->content = geefEen('SELECT tekst FROM ' . $this->vvstring . 'subs WHERE id=?', array($this->id));
            $this->titel = geefEen('SELECT naam FROM ' . $this->vvstring . 'subs WHERE id=?', array($this->id));
        }

    }

    protected function toonSpecifiekeKnoppen()
    {
        $checked = geefEen('SELECT reacties_aan FROM subs WHERE id=?', array($this->id)) ? ' checked="checked"' : '';
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="reacties_aan">Reacties aan: </label>
            <div class="col-sm-5">
                <input id="reacties_aan" name="reacties_aan" type="checkbox" <?=$checked;?>/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label" for="categorieid">Plaats dit artikel in de categorie: </label>
            <div class="col-sm-5">
                <select name="categorieid" class="form-control"><option value="0">&lt;Geen categorie&gt;</option>
                    <?php

                    if ($this->id)
                        $categorieid = geefEen('SELECT categorieid FROM subs WHERE id= ?', array($this->id));
                    else
                        $categorieid = geefInstelling('standaardcategorie');

                    $categorieen = $this->connectie->query("SELECT * FROM categorieen ORDER BY naam;");
                    foreach ($categorieen->fetchAll() as $categorie)
                    {
                        $selected = ($categorieid == $categorie['id']) ? ' selected="selected"' : '';
                        printf('<option value="%d" %s>%s</option>', $categorie['id'], $selected,  $categorie['naam']);
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
    }
}