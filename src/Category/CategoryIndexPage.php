<?php
namespace Cyndaron\Category;

use Cyndaron\Error\ErrorPage;
use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\StaticPage\StaticPageModel;
use Cyndaron\StaticPage\StaticPageRepository;
use Cyndaron\Url\UrlService;
use Cyndaron\User\UserLevel;
use Cyndaron\View\Renderer\TextRenderer;
use Symfony\Component\HttpFoundation\Response;
use function count;

final class CategoryIndexPage
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly TextRenderer $textRenderer,
        private readonly UrlService $urlService,
        private readonly StaticPageRepository $staticPageRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function show(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(1);
        if ($id < 1)
        {
            $page = Page::createSimple('Foute aanvraag', 'Incorrecte parameter ontvangen.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }

        $category = $this->categoryRepository->fetchById($id);
        if ($category === null)
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Fout', 'Categorie niet gevonden!', Response::HTTP_NOT_FOUND));
        }

        $page = new Page();
        $page->title = $category->name;
        $page->template = 'Category/CategoryPage';
        $page->model = $category;
        $page->category = $this->categoryRepository->getFirstLinkedCategory($category);

        $subs = $this->staticPageRepository->fetchAllByCategory($category, 'ORDER BY id DESC');

        $page->addTemplateVars([
            'type' => 'subs',
            'model' => $category,
            'parsedDescription' => $this->textRenderer->render($category->description),
            'viewMode' => $category->viewMode,
            'pages' => $this->categoryRepository->getUnderlyingPages($category),
            'tags' => $this->getTags($subs),
            'portfolioContent' => $this->getPortfolioContent($category),
            'pageImage' => $category->getImage(),
            'urlService' => $this->urlService,
        ]);

        return $this->pageRenderer->renderResponse($page);
    }

    /**
     * @param StaticPageModel[] $subs
     * @return string[]
     */
    private function getTags(array $subs): array
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
    private function getPortfolioContent(Category $category): array
    {
        $portfolioContent = [];

        if ($category->viewMode === ViewMode::Portfolio)
        {
            $subCategories = $this->categoryRepository->fetchAllByCategory($category);
            foreach ($subCategories as $subCategory)
            {
                $subs = $this->staticPageRepository->fetchAllByCategory($category, 'ORDER BY id DESC');
                $portfolioContent[$subCategory->name] = $subs;
            }
        }

        return $portfolioContent;
    }
}
