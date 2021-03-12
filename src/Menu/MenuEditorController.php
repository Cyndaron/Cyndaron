<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

final class MenuEditorController extends Controller
{
    protected int $minLevelGet = UserLevel::ADMIN;

    protected function routeGet(QueryBits $queryBits): Response
    {
        $page = new MenuEditorPage();
        return new Response($page->render());
    }
}
