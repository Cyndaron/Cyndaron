<?php
namespace Cyndaron\Category;

use Cyndaron\Page\Page;
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
            'model' => $category,
            'viewMode' => $category->viewMode,
            'pages' => $category->getUnderlyingPages(),
            'tags' => $this->getTags($subs),
            'portfolioContent' => $this->getPortfolioContent(),
            'pageImage' => $category->getImage(),
        ]);
    }

    /**
     * @param StaticPageModel[] $subs
     * @return string[]
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

    /**
     * @throws \Exception
     * @return array<string, list<StaticPageModel>>
     */
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
