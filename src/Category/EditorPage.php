<?php
namespace Cyndaron\Category;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'category';
    public const TABLE = 'categories';
    public const HAS_CATEGORY = true;
    public const SAVE_URL = '/editor/category/%s';

    protected string $template = '';

    protected function prepare(): void
    {
        $currentViewMode = Category::VIEWMODE_REGULAR;
        if ($this->id)
        {
            $this->model = Category::loadFromDatabase($this->id);
            $this->content = $this->model->description ?? '';
            $this->contentTitle = $this->model->name ?? '';
            $currentViewMode = $this->model->viewMode ?? Category::VIEWMODE_REGULAR;
        }

        $this->addTemplateVars([
            'id' => 'viewMode',
            'label' => 'Weergave',
            'options' => Category::VIEWMODE_DESCRIPTIONS,
            'selected' => $currentViewMode,
        ]);
    }
}
