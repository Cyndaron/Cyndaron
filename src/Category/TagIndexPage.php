<?php
declare(strict_types=1);

namespace Cyndaron\Category;

use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\StaticPage\StaticPageRepository;
use Cyndaron\Url\UrlService;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;
use function in_array;
use function strtolower;
use function ucfirst;

final class TagIndexPage
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly UrlService $urlService,
        private readonly StaticPageRepository $staticPageRepository
    ) {

    }

    #[RouteAttribute('tag', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function show(QueryBits $queryBits): Response
    {
        $tag = $queryBits->getString(2);
        if ($tag === '')
        {
            $page = Page::createSimple('Foute aanvraag', 'Lege tag ontvangen.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }

        $page = new Page();
        $page->template = 'Category/CategoryPage';

        $page->title = ucfirst($tag);

        $tags = [];
        $pages = [];

        $subs = $this->staticPageRepository->fetchAllByTag($tag);
        foreach ($subs as $sub)
        {
            $tagList = $sub->getTagList();
            if ($tagList !== [])
            {
                $tags += $tagList;
                if (in_array(strtolower($tag), $tagList, true))
                {
                    $pages[] = $sub;
                }
            }
        }

        $page->addTemplateVars([
            'type' => 'tag',
            'pages' => $pages,
            'tags' => $tags,
            'viewMode' => ViewMode::Blog,
            'urlService' => $this->urlService,
        ]);

        return $this->pageRenderer->renderResponse($page);
    }
}
