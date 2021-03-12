<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

final class PageManagerController extends Controller
{
    protected int $minLevelGet = UserLevel::ADMIN;

    protected function routeGet(QueryBits $queryBits): Response
    {
        $currentPage = $queryBits->getString(1, 'sub');
        $page = new PageManagerPage($currentPage);
        return new Response($page->render());
    }
}
