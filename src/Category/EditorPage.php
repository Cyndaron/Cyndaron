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
        $checked = false;
        if ($this->id)
            $checked = (bool)DBConnection::doQueryAndFetchOne('SELECT viewMode FROM categories WHERE id=?', [$this->id]);
        $this->showCheckbox('alleentitel', 'Toon alleen titels', $checked);
    }
}