<?php
$heeftTitel = true;

if ($id)
{
    $content = geefEen('SELECT beschrijving FROM categorieen WHERE id=?', array($id));
    $titel = geefEen('SELECT naam FROM categorieen WHERE id=?', array($id));
}

function toonSpecifiekeKnoppen()
{
    global $id;
    $checked = geefEen('SELECT alleentitel FROM categorieen WHERE id=?', array($id)) ? 'checked="checked"' : '';
    ?>
    <div class="form-group">
        <label class="col-sm-2 control-label" for="alleentitel">Toon alleen titels: </label>
        <div class="col-sm-5">
            <input id="alleentitel" name="alleentitel" type="checkbox" <?=$checked;?>/>
        </div>
    </div>
    <?php
}