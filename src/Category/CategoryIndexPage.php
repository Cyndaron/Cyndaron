<?php
namespace Cyndaron\Category;

use Cyndaron\Page;
use Cyndaron\StaticPage\StaticPageModel;

class CategoryIndexPage extends Page
{
    protected string $template = 'Category/CategoryPage';

    public function __construct(Category $category)
    {
        $this->model = $category;

        parent::__construct($this->model->name);

        $subs = StaticPageModel::fetchAll(['categoryId= ?'], [$category->id], 'ORDER BY id DESC');

        $this->renderAndEcho([
            'type' => 'subs',
            'model' => $this->model,
            'viewMode' => $this->model->viewMode,
            'pages' => $subs,
            'tags' => $this->getTags($subs),
            'portfolioContent' => $this->getPortfolioContent(),
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

        if ($this->model->viewMode === Category::VIEWMODE_PORTFOLIO)
        {
            $subCategories = Category::fetchAll(['categoryId = ?'], [$this->model->id]);
            foreach ($subCategories as $subCategory)
            {
                $subs = StaticPageModel::fetchAll(['categoryId = ?'], [$subCategory->id], 'ORDER BY id DESC');
                $portfolioContent[$subCategory->name] = $subs;
            }
        }

        return $portfolioContent;
    }
}