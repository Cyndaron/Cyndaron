<?php
declare (strict_types = 1);

namespace Cyndaron\StaticPage;

use Cyndaron\Controller;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Model;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class StaticPageController extends Controller
{
    protected array $postRoutes = [
        'addtomenu' => ['level' => UserLevel::ADMIN, 'function' => 'addToMenu'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'react' => ['level' => UserLevel::ANONYMOUS, 'function' => 'react'],
    ];

    protected function routeGet(): Response
    {
        $id = $this->queryBits->getInt(1);
        $model = StaticPageModel::loadFromDatabase($id);
        if ($model === null)
        {
            $page = new Page('Fout', 'Statische pagina niet gevonden.');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }
        $page = new StaticPage($model);
        return new Response($page->render());
    }

    protected function addToMenu(): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        $menuItem = new MenuItem();
        $menuItem->link = '/sub/' . $id;
        $menuItem->save();
        return new JsonResponse();
    }

    protected function delete(): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        $model = new StaticPageModel($id);
        $model->delete();
        return new JsonResponse();
    }

    protected function react(): Response
    {
        $id = $this->queryBits->getInt(2);
        $model = Model::loadFromDatabase($id);
        if ($model === null)
        {
            $page = new Page('Fout', 'Statische pagina niet gevonden.');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }

        $author = Request::post('author');
        $reactie = Request::post('reactie');
        $antispam = strtolower(Request::post('antispam'));
        $model->react($author, $reactie, $antispam);

        return new RedirectResponse("/sub/$id");
    }
}