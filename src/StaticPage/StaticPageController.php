<?php
declare(strict_types=1);

namespace Cyndaron\StaticPage;

use Cyndaron\Menu\MenuItem;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\View\Renderer\TextRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function strtolower;

final class StaticPageController extends Controller
{
    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function routeGet(QueryBits $queryBits, TextRenderer $textRenderer): Response
    {
        $id = $queryBits->getInt(1);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $model = StaticPageModel::fetchById($id);
        if ($model === null)
        {
            $page = new SimplePage('Fout', 'Statische pagina niet gevonden.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }
        $page = new StaticPage($model, $textRenderer);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('addtomenu', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function addToMenu(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $menuItem = new MenuItem();
        $menuItem->link = '/sub/' . $id;
        $menuItem->save();
        return new JsonResponse();
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $model = new StaticPageModel($id);
        $model->delete();
        return new JsonResponse();
    }

    #[RouteAttribute('react', RequestMethod::POST, UserLevel::ANONYMOUS)]
    public function react(QueryBits $queryBits, RequestParameters $post): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $model = StaticPageModel::fetchById($id);
        if ($model === null)
        {
            $page = new SimplePage('Fout', 'Statische pagina niet gevonden.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        $author = $post->getHTML('author');
        $reactie = $post->getHTML('reactie');
        $antispam = strtolower($post->getAlphaNum('antispam'));
        $model->react($author, $reactie, $antispam);

        return new RedirectResponse("/sub/$id");
    }
}
