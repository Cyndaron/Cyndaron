<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Error\ErrorPage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

final class PageManagerController extends Controller
{
    public array $getRoutes = [
        '' => ['level' => UserLevel::LOGGED_IN, 'function' => 'routeGet'],
    ];

    protected function routeGet(QueryBits $queryBits, User $currentUser, ModuleRegistry $registry): Response
    {
        $currentPage = $queryBits->getString(1, 'sub');
        try
        {
            $page = new PageManagerPage($currentUser, $currentPage, $registry);
            return $this->pageRenderer->renderResponse($page);
        }
        catch (\RuntimeException)
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Paginabeheer', 'Er zijn geen datatypes die u kunt beheren!', Response::HTTP_NOT_FOUND));
        }
    }
}
