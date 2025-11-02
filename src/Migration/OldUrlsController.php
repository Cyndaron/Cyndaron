<?php
declare(strict_types=1);

namespace Cyndaron\Migration;

use Cyndaron\Error\ErrorPage;
use Cyndaron\Mailform\MailformController;
use Cyndaron\Mailform\MailformRepository;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\MailFactory;
use Cyndaron\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OldUrlsController
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
    ) {
    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function routeGet(Request $request, QueryBits $queryBits): Response
    {
        switch ($queryBits->getString(0))
        {
            case 'tooncategorie.php':
                return $this->redirectOldCategoryUrl($request);
            case 'toonfoto.php':
                return $this->redirectOldPhotoUrl($request);
            case 'toonfotoboek.php':
                return $this->redirectOldPhotoalbumUrl($request);
            case 'toonsub.php':
                return $this->redirectOldStaticPageUrl($request);
        }

        return $this->pageRenderer->renderErrorResponse(new ErrorPage('Routingfout', 'Niet-herkende oude URL.'));
    }

    #[RouteAttribute('', RequestMethod::POST, UserLevel::ANONYMOUS, skipCSRFCheck: true)]
    public function routePost(RequestParameters $requestParameters, Request $request, MailFactory $mailFactory, QueryBits $queryBits, MailformRepository $mailformRepository): Response
    {
        if ($queryBits->getString(0) !== 'verwerkmailformulier.php')
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Routingfout', 'Niet-herkende oude URL.'));
        }

        $id = $request->query->getInt('id');
        $controller = new MailformController($this->pageRenderer, $mailformRepository);
        $queryBits = new QueryBits(['mailform', 'process', (string)$id]);
        return $controller->process($queryBits, $requestParameters, $mailFactory);
    }

    public function redirectOldStaticPageUrl(Request $request): Response
    {
        $id = $request->query->getInt('id');
        return new RedirectResponse("/sub/$id");
    }

    public function redirectOldCategoryUrl(Request $request): Response
    {
        $id = $request->query->getInt('id');
        return new RedirectResponse("/category/$id");
    }

    public function redirectOldPhotoalbumUrl(Request $request): Response
    {
        $id = $request->query->getInt('id');
        return new RedirectResponse("/photoalbum/$id");
    }

    public function redirectOldPhotoUrl(Request $request): Response
    {
        $id = $request->query->getInt('boekid');
        return new RedirectResponse("/photoalbum/$id");
    }
}
