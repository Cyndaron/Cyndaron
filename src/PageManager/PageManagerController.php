<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Cyndaron\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

final class PageManagerController extends Controller
{
    protected int $minLevelGet = UserLevel::ADMIN;

    protected function routeGet(): Response
    {
        $currentPage = $this->queryBits->getString(1, 'sub');
        $page = new PageManagerPage($currentPage);
        return new Response($page->render());
    }
}
