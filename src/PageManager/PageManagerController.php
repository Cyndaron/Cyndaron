<?php
declare (strict_types = 1);

namespace Cyndaron\PageManager;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class PageManagerController extends Controller
{
    protected int $minLevelGet = UserLevel::ADMIN;

    protected function routeGet(): Response
    {
        $currentPage = Request::getVar(1) ?: 'sub';
        $page = new PageManagerPage($currentPage);
        return new Response($page->render());
    }
}