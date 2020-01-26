<?php
declare (strict_types = 1);

namespace Cyndaron\PageManager;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;

class PageManagerController extends Controller
{
    protected int $minLevelGet = UserLevel::ADMIN;

    protected function routeGet()
    {
        $currentPage = Request::getVar(1) ?: 'sub';
        new PageManagerPage($currentPage);
    }
}