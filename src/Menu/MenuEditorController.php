<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class MenuEditorController extends Controller
{
    protected int $minLevelGet = UserLevel::ADMIN;

    protected function routeGet(): Response
    {
        $page = new MenuEditorPage();
        return new Response($page->render());
    }
}