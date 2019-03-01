<?php
namespace Cyndaron\Category;

use Cyndaron\DBConnection;

class EditorPage extends \Cyndaron\EditorPage
{
    protected $type = 'category';
    protected $table = 'categorieen';
    protected $saveUrl = '/editor/category/%s';

    protected function prepare()
    {
        if ($this->id)
        {
            $this->content = DBConnection::doQueryAndFetchOne('SELECT beschrijving FROM categorieen WHERE id=?', [$this->id]);
            $this->contentTitle = DBConnection::doQueryAndFetchOne('SELECT naam FROM categorieen WHERE id=?', [$this->id]);
        }
    }

    protected function showContentSpecificButtons()
    {
        $checked = DBConnection::doQueryAndFetchOne('SELECT alleentitel FROM categorieen WHERE id=?', [$this->id]) ? 'checked="checked"' : '';
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