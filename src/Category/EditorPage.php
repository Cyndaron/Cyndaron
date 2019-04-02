<?php
namespace Cyndaron\Category;

use Cyndaron\DBConnection;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    protected $type = 'category';
    protected $table = 'categorieen';
    protected $saveUrl = '/editor/category/%s';
    const HAS_CATEGORY = true;

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
        $checked = false;
        if ($this->id)
            $checked = (bool)DBConnection::doQueryAndFetchOne('SELECT alleentitel FROM categorieen WHERE id=?', [$this->id]);
        $this->showCheckbox('alleentitel', 'Toon alleen titels', $checked);

        $this->showCategoryDropdown();
    }
}