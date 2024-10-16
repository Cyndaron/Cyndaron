<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Error\ErrorPage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\DependencyInjectionContainer;
use Cyndaron\Util\RuntimeUserSafeError;
use Symfony\Component\HttpFoundation\Response;

final class PageManagerController extends Controller
{
    #[RouteAttribute('', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function routeGet(QueryBits $queryBits, DependencyInjectionContainer $dic, User $currentUser, ModuleRegistry $registry): Response
    {
        $currentPage = $queryBits->getString(1, 'sub');
        try
        {
            $page = new PageManagerPage($dic, $currentUser, $currentPage, $registry);
            return $this->pageRenderer->renderResponse($page);
        }
        catch (RuntimeUserSafeError $e)
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Paginabeheer', $e->getMessage(), Response::HTTP_NOT_FOUND));
        }
    }
}
