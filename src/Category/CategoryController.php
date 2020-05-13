<?php
declare (strict_types = 1);

namespace Cyndaron\Category;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Request;

class CategoryController extends Controller
{
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

    protected function routePost()
    {
        $id = (int)Request::getVar(2);
        $return = [];

        switch ($this->action)
        {
            case 'add':
                $category = new Category(null);
                $category->name = Request::post('name');
                $result = $category->save();
                if ($result === false) {
                    $return = DBConnection::errorInfo();
                }
                break;
            case 'edit':
                $category = new Category($id);
                $category->load();
                $category->name = Request::post('name');
                $category->save();
                break;
            case 'delete':
                $category = new Category($id);
                $category->delete();
                break;
            case 'addtomenu':
                $menuItem = new MenuItem();
                $menuItem->link = '/category/' . $id;
                $result = $menuItem->save();
                if ($result === false)
                {
                    $return = DBConnection::errorInfo();
                }
                break;
        }

        echo json_encode($return);
    }
}