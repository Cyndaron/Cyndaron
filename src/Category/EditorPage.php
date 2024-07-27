<?php
namespace Cyndaron\Category;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'category';
    public const HAS_CATEGORY = true;
    public const SAVE_URL = '/editor/category/%s';

    public string $template = '';

    protected function prepare(): void
    {
        $currentViewMode = ViewMode::Regular;
        if ($this->id)
        {
            $this->model = Category::fetchById($this->id);
            $this->content = $this->model->description ?? '';
            $this->contentTitle = $this->model->name ?? '';
            $currentViewMode = $this->model->viewMode ?? ViewMode::Regular;
        }

        $this->addTemplateVars([
            'id' => 'viewMode',
            'label' => 'Weergave',
            'options' => ViewMode::cases(),
            'selected' => $currentViewMode,
        ]);
    }
}
