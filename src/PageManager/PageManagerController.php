<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Cyndaron\Error\ErrorPageResponse;
use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

final class PageManagerController extends Controller
{
    protected int $minLevelGet = UserLevel::LOGGED_IN;

    protected function routeGet(QueryBits $queryBits, User $currentUser): Response
    {
        $currentPage = $queryBits->getString(1, 'sub');
        try
        {
            $page = new PageManagerPage($currentUser, $currentPage);
            return new Response($page->render());
        }
        catch (\RuntimeException)
        {
            return new ErrorPageResponse('Paginabeheer', 'Er zijn geen datatypes die u kunt beheren!', Response::HTTP_NOT_FOUND);
        }
    }
}
