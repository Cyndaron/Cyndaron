<?php
declare (strict_types = 1);

namespace Cyndaron\Category;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    protected array $apiPostRoutes = [
        'add' => ['level' => UserLevel::ADMIN, 'function' => 'add'],
        'addtomenu' => ['level' => UserLevel::ADMIN, 'function' => 'addToMenu'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'edit' => ['level' => UserLevel::ADMIN, 'function' => 'edit'],
    ];

    protected function routeGet(): Response
    {
        $id = Request::getVar(1);

        if ($id === '0' || $id === 'fotoboeken')
        {
            $page = new PhotoalbumIndexPage();
            return new Response($page->render());
        }
        if ($id === 'tag')
        {
            $tag = Request::getVar(2);
            $page = new TagIndexPage($tag);
            return new Response($page->render());
        }
        if ($id < 0)
        {
            $page = new Page('Foute aanvraag', 'Incorrecte parameter ontvangen.');
            return new Response($page->render());
        }

        $category = Category::loadFromDatabase((int)$id);
        $page = new CategoryIndexPage($category);
        return new Response($page->render());
    }

    public function add(): JsonResponse
    {
        $return = [];
        $category = new Category(null);
        $category->name = Request::post('name');
        $result = $category->save();
        if ($result === false) {
            $return = DBConnection::errorInfo();
        }

        return new JsonResponse($return);
    }

    public function addToMenu(): JsonResponse
    {
        $id = (int)Request::getVar(2);
        $return = [];
        $menuItem = new MenuItem();
        $menuItem->link = '/category/' . $id;
        $result = $menuItem->save();
        if ($result === false)
        {
            $return = DBConnection::errorInfo();
        }

        return new JsonResponse($return);
    }

    public function delete(): JsonResponse
    {
        $id = (int)Request::getVar(2);
        $category = new Category($id);
        $category->delete();

        return new JsonResponse();
    }

    public function edit(): JsonResponse
    {
        $id = (int)Request::getVar(2);
        $category = new Category($id);
        $category->load();
        $category->name = Request::post('name');
        $category->save();

        return new JsonResponse();
    }

}