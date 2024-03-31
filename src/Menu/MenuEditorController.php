<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\Routing\Controller;
use Cyndaron\Url\UrlService;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

final class MenuEditorController extends Controller
{
    public array $getRoutes = [
        '' => ['level' => UserLevel::ADMIN, 'function' => 'routeGet'],
    ];

    protected function routeGet(UrlService $urlService): Response
    {
        $page = new MenuEditorPage($urlService);
        return $this->pageRenderer->renderResponse($page);
    }
}
