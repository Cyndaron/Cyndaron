<?php
namespace Cyndaron\Category;

use Cyndaron\DBConnection;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'category';
    public const TABLE = 'categories';
    public const HAS_CATEGORY = true;
    public const SAVE_URL = '/editor/category/%s';

    protected string $template = '';

    protected function prepare()
    {
        $viewMode = 0;
        if ($this->id)
        {
            $this->model = Category::loadFromDatabase($this->id);
            $this->content = $this->model->description ?? '';
            $this->contentTitle = $this->model->name ?? '';
            $viewMode = ($this->model->viewMode) ?? Category::VIEWMODE_REGULAR;
        }

        $id = 'viewMode';
        $label = 'Weergave';
        $options = Category::VIEWMODE_DESCRIPTIONS;
        $selected = $viewMode;

        $this->addTemplateVars(compact('id', 'label', 'options', 'selected'));
    }
}
