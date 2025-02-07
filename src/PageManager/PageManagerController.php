<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Cyndaron\Error\ErrorPage;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\DependencyInjectionContainer;
use Cyndaron\Util\RuntimeUserSafeError;
use Symfony\Component\HttpFoundation\Response;

final class PageManagerController
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
    ) {
    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function routeGet(QueryBits $queryBits, DependencyInjectionContainer $dic, PageManagerPage $page): Response
    {
        $currentPage = $queryBits->getString(1, 'sub');
        try
        {
            return $this->pageRenderer->renderResponse($page->createPage($dic, $currentPage));
        }
        catch (RuntimeUserSafeError $e)
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Paginabeheer', $e->getMessage(), Response::HTTP_NOT_FOUND));
        }
    }
}
