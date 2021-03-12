<?php
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\Error\ErrorPageResponse;
use Cyndaron\Mailform\MailformController;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OldUrlsController extends Controller
{
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

        return new ErrorPageResponse('Routingfout', 'Niet-herkende oude URL.');
    }

    public function routePost(RequestParameters $requestParameters, Request $request): Response
    {
        if ($this->module !== 'verwerkmailformulier.php')
        {
            return new ErrorPageResponse('Routingfout', 'Niet-herkende oude URL.');
        }

        $id = $request->query->getInt('id');
        $controller = new MailformController('mailform', 'process', false);
        $queryBits = new QueryBits(['mailform', 'process', $id]);
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
