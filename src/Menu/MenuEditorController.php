<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Url\UrlService;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

final class MenuEditorController
{
    public function __construct(
        protected readonly PageRenderer $pageRenderer,
    ) {
    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::ADMIN)]
    public function routeGet(UrlService $urlService, MenuItemRepository $menuItemRepository): Response
    {
        $page = new MenuEditorPage($urlService, $menuItemRepository);
        return $this->pageRenderer->renderResponse($page);
    }
}
