<?php
namespace Cyndaron\Category;

use Cyndaron\Page;
use Cyndaron\StaticPage\StaticPageModel;
use function count;

final class CategoryIndexPage extends Page
{
    protected string $template = 'Category/CategoryPage';

    public function __construct(Category $category)
    {
        $this->model = $category;

        parent::__construct($this->model->name);

        $subs = StaticPageModel::fetchAllByCategory($category, 'ORDER BY id DESC');

        $this->addTemplateVars([
            'type' => 'subs',
            'model' => $this->model,
            'viewMode' => $this->model->viewMode,
            'pages' => $category->getUnderlyingPages(),
            'tags' => $this->getTags($subs),
            'portfolioContent' => $this->getPortfolioContent(),
            'pageImage' => $this->model->getImage(),
        ]);
    }

    /**
     * @param StaticPageModel[] $subs
     * @return array
     */
    protected function getTags(array $subs): array
    {
        $tags = [];
        foreach ($subs as $sub)
        {
            $tagList = $sub->getTagList();
            if (count($tagList) > 0)
            {
                $tags += $tagList;
            }
        }
        return $tags;
    }

    protected function getPortfolioContent(): array
    {
        $portfolioContent = [];

        if ($this->model instanceof Category && $this->model->viewMode === Category::VIEWMODE_PORTFOLIO)
        {
            $subCategories = Category::fetchAllByCategory($this->model);
            foreach ($subCategories as $subCategory)
            {
                $subs = StaticPageModel::fetchAllByCategory($this->model, 'ORDER BY id DESC');
                $portfolioContent[$subCategory->name] = $subs;
            }
        }

        return $portfolioContent;
    }
}
