<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

final class MenuEditorController extends Controller
{
    protected array $getRoutes = [
        '' => ['level' => UserLevel::ADMIN, 'function' => 'routeGet'],
    ];

    protected function routeGet(QueryBits $queryBits): Response
    {
        $page = new MenuEditorPage();
        return new Response($page->render());
    }
}
