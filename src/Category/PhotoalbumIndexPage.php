<?php
namespace Cyndaron\Category;

use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Photoalbum\PhotoalbumRepository;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Url\UrlService;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

final class PhotoalbumIndexPage
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly UrlService $urlService,
        private readonly PhotoalbumRepository $photoalbumRepository
    ) {
    }

    #[RouteAttribute('0', RequestMethod::GET, UserLevel::ANONYMOUS)]
    #[RouteAttribute('fotoboeken', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function viewPhotoalbumIndex(): Response
    {
        $page = new Page();
        $page->title = 'Fotoalbums';
        $page->template = 'Category/CategoryPage';
        $photoalbums = $this->photoalbumRepository->fetchAll(['hideFromOverview = 0'], [], 'ORDER BY id DESC');

        $page->addTemplateVars([
            'type' => 'photoalbums',
            'model' => null,
            'pages' => $photoalbums,
            'tags' => [],
            'viewMode' => ViewMode::Blog,
            'urlService' => $this->urlService,
        ]);

        return $this->pageRenderer->renderResponse($page);
    }
}
