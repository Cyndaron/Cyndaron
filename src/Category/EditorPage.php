<?php
namespace Cyndaron\Category;

use Cyndaron\DBConnection;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'category';
    const TABLE = 'categories';
    const HAS_CATEGORY = true;
    const SAVE_URL = '/editor/category/%s';

    protected $template = '';

    protected function prepare()
    {
        $viewMode = 0;
        if ($this->id)
        {
            $this->content = DBConnection::doQueryAndFetchOne('SELECT description FROM categories WHERE id=?', [$this->id]);
            $this->contentTitle = DBConnection::doQueryAndFetchOne('SELECT name FROM categories WHERE id=?', [$this->id]);
            $viewMode = (int)DBConnection::doQueryAndFetchOne('SELECT viewMode FROM categories WHERE id=?', [$this->id]);
        }

        $id = 'viewMode';
        $label = 'Weergave';
        $options = Category::VIEWMODE_DESCRIPTIONS;
        $selected = $viewMode;

        $this->templateVars = array_merge($this->templateVars, compact('id', 'label', 'options', 'selected'));
    }

    protected function showContentSpecificButtons()
    {
    }
}