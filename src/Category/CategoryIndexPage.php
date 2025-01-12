<?php
namespace Cyndaron\Category;

use Cyndaron\Page\Page;
use Cyndaron\StaticPage\StaticPageModel;
use Cyndaron\Url\UrlService;
use Cyndaron\View\Renderer\TextRenderer;
use function count;

final class CategoryIndexPage extends Page
{
    public string $template = 'Category/CategoryPage';

    public function __construct(UrlService $urlService, Category $category, TextRenderer $textRenderer)
    {
        $this->model = $category;

        $this->title = $this->model->name;

        $subs = StaticPageModel::fetchAllByCategory($category, 'ORDER BY id DESC');

        $this->addTemplateVars([
            'type' => 'subs',
            'model' => $category,
            'parsedDescription' => $textRenderer->render($category->description),
            'viewMode' => $category->viewMode,
            'pages' => $category->getUnderlyingPages(),
            'tags' => $this->getTags($subs),
            'portfolioContent' => $this->getPortfolioContent(),
            'pageImage' => $category->getImage(),
            'urlService' => $urlService,
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

        if ($this->model instanceof Category && $this->model->viewMode === ViewMode::Portfolio)
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
