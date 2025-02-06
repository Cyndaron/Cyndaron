<?php
declare(strict_types=1);

namespace Cyndaron\Category;

use function assert;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'category';
    public const HAS_CATEGORY = true;
    public const SAVE_URL = '/editor/category/%s';

    public string $template = '';

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {

    }

    public function prepare(): void
    {
        $currentViewMode = ViewMode::Regular;
        if ($this->id)
        {
            $category = $this->categoryRepository->fetchById($this->id);
            assert($category !== null);
            $this->model = $category;
            $this->linkedCategories = $this->categoryRepository->getLinkedCategories($category);
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
