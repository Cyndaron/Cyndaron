<?php
namespace Cyndaron\Category;

use Cyndaron\DBConnection;

class EditorPage extends \Cyndaron\EditorPage
{
    protected $heeftTitel = true;
    protected $type = 'categorie';
    protected $table = 'categorieen';
    protected $saveUrl = '/editor/category/%s';

    protected function prepare()
    {
        if ($this->id)
        {
            $this->content = DBConnection::geefEen('SELECT beschrijving FROM categorieen WHERE id=?', [$this->id]);
            $this->titel = DBConnection::geefEen('SELECT naam FROM categorieen WHERE id=?', [$this->id]);
        }
    }

    protected function toonSpecifiekeKnoppen()
    {
        $checked = DBConnection::geefEen('SELECT alleentitel FROM categorieen WHERE id=?', [$this->id]) ? 'checked="checked"' : '';
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="alleentitel">Toon alleen titels: </label>
            <div class="col-sm-5">
                <input id="alleentitel" name="alleentitel" type="checkbox" <?= $checked; ?>/>
            </div>
        </div>
        <?php
        $this->showCategoryDropdown();
    }
}