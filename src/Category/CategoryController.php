<?php
declare (strict_types = 1);

namespace Cyndaron\Category;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Request;
use Cyndaron\Response\JSONResponse;
use Cyndaron\User\UserLevel;

class CategoryController extends Controller
{
    protected array $apiPostRoutes = [
        'add' => ['level' => UserLevel::ADMIN, 'function' => 'add'],
        'addtomenu' => ['level' => UserLevel::ADMIN, 'function' => 'addToMenu'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'edit' => ['level' => UserLevel::ADMIN, 'function' => 'edit'],
    ];

    protected function routeGet()
    {
        $id = Request::getVar(1);

        if ($id === '0' || $id === 'fotoboeken')
        {
            $page = new PhotoalbumIndexPage();
        }
        elseif ($id === 'tag')
        {
            $tag = Request::getVar(2);
            $page = new TagIndexPage($tag);
        }
        elseif ($id < 0)
        {
            header('Location: /error/404');
            die('Incorrecte parameter ontvangen.');
        }
        else
        {
            $category = Category::loadFromDatabase((int)$id);
            $page = new CategoryIndexPage($category);
        }
    }

    public function add(): JSONResponse
    {
        $return = [];
        $category = new Category(null);
        $category->name = Request::post('name');
        $result = $category->save();
        if ($result === false) {
            $return = DBConnection::errorInfo();
        }

        return new JSONResponse($return);
    }

    public function addToMenu(): JSONResponse
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

        return new JSONResponse($return);
    }

    public function delete(): JSONResponse
    {
        $id = (int)Request::getVar(2);
        $category = new Category($id);
        $category->delete();

        return new JSONResponse();
    }

    public function edit(): JSONResponse
    {
        $id = (int)Request::getVar(2);
        $category = new Category($id);
        $category->load();
        $category->name = Request::post('name');
        $category->save();

        return new JSONResponse();
    }

}