<?php
declare(strict_types=1);

namespace Cyndaron\Migration;

use Cyndaron\Error\ErrorPage;
use Cyndaron\Mailform\MailformController;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OldUrlsController extends Controller
{
    public array $getRoutes = [
        '' => ['level' => UserLevel::ANONYMOUS, 'function' => 'routeGet'],
    ];
    public array $postRoutes = [
        '' => ['level' => UserLevel::ANONYMOUS, 'function' => 'routePost', 'skipCSRFCheck' => true],
    ];

    public function routeGet(Request $request): Response
    {
        switch ($this->module)
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

    public function routePost(RequestParameters $requestParameters, Request $request): Response
    {
        if ($this->module !== 'verwerkmailformulier.php')
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Routingfout', 'Niet-herkende oude URL.'));
        }

        $id = $request->query->getInt('id');
        $controller = new MailformController('mailform', 'process', false, $this->templateRenderer, $this->pageRenderer);
        $queryBits = new QueryBits(['mailform', 'process', (string)$id]);
        return $controller->process($queryBits, $requestParameters);
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
