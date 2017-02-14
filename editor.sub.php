<?php
$heeftTitel = true;

if ($id)
{
    $content = geefEen('SELECT tekst FROM ' . $vvstring . 'subs WHERE id=?', array($id));
    $titel = geefEen('SELECT naam FROM ' . $vvstring . 'subs WHERE id=?', array($id));
}

function toonSpecifiekeKnoppen()
{
    global $id;
    $checked = geefEen('SELECT reacties_aan FROM subs WHERE id=?', array($id)) ? ' checked="checked"' : '';
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
            $connectie = newPDO();

            if ($id)
                $categorieid = geefEen('SELECT categorieid FROM subs WHERE id= ?', array($id));
            else
                $categorieid = geefInstelling('standaardcategorie');

            $categorieen = $connectie->query("SELECT * FROM categorieen ORDER BY naam;");
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
