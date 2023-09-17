<?php
declare(strict_types=1);

namespace Cyndaron\StaticPage;

use Cyndaron\Menu\MenuItem;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function strtolower;

final class StaticPageController extends Controller
{
    protected array $postRoutes = [
        'addtomenu' => ['level' => UserLevel::ADMIN, 'function' => 'addToMenu'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'react' => ['level' => UserLevel::ANONYMOUS, 'function' => 'react'],
    ];

    protected function routeGet(QueryBits $queryBits): Response
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
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }
        $page = new StaticPage($model);
        return new Response($page->render());
    }

    protected function addToMenu(QueryBits $queryBits): JsonResponse
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

    protected function delete(QueryBits $queryBits): JsonResponse
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

    protected function react(QueryBits $queryBits, RequestParameters $post): Response
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
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }

        $author = $post->getHTML('author');
        $reactie = $post->getHTML('reactie');
        $antispam = strtolower($post->getAlphaNum('antispam'));
        $model->react($author, $reactie, $antispam);

        return new RedirectResponse("/sub/$id");
    }
}
