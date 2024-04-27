<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Url\UrlService;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

final class MenuEditorController extends Controller
{
    #[RouteAttribute('', RequestMethod::GET, UserLevel::ADMIN)]
    protected function routeGet(UrlService $urlService): Response
    {
        $page = new MenuEditorPage($urlService);
        return $this->pageRenderer->renderResponse($page);
    }
}
