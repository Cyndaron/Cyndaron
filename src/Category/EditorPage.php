<?php
namespace Cyndaron\Category;

use Cyndaron\DBConnection;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'category';
    const TABLE = 'categorieen';
    const HAS_CATEGORY = true;
    const SAVE_URL = '/editor/category/%s';

    protected function prepare()
    {
        if ($this->id)
        {
            $this->content = DBConnection::doQueryAndFetchOne('SELECT description FROM categories WHERE id=?', [$this->id]);
            $this->contentTitle = DBConnection::doQueryAndFetchOne('SELECT name FROM categories WHERE id=?', [$this->id]);
        }
    }

    protected function showContentSpecificButtons()
    {
        $viewMode = 0;
        if ($this->id)
            $viewMode = (int)DBConnection::doQueryAndFetchOne('SELECT viewMode FROM categories WHERE id=?', [$this->id]);

        $id = 'viewMode';
        $label = 'Weergave';
        $options = [0 => 'Samenvatting', 1 => 'Alleen titels', 2 => 'Blog', 3 => 'Portfolio'];
        $selected = $viewMode;

        ?>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="<?=$id?>"><?=$label?>: </label>
            <div class="col-sm-5">
                <select id="<?=$id?>" name="<?=$id?>" class="form-control custom-select">
                    <?php foreach ($options as $value => $description): ?>
                    <option value="<?=$value?>" <?php if ($value == $selected):?>selected<?php endif;?>><?=$description?></option>
                    <?php endforeach;?>
                </select>
            </div>
        </div>
        <?php
    }
}