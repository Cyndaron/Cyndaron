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

        if ($id === '0' || $id == 'fotoboeken')
        {
            $page = new CategoryPage();
            $page->showPhotoalbumsIndex();
        }
        elseif ($id == 'tag')
        {
            $page = new CategoryPage();
            $page->showTagIndex(Request::getVar(2));
        }
        elseif ($id < 0)
        {
            header("Location: /error/404");
            die('Incorrecte parameter ontvangen.');
        }
        else
        {
            $category = Category::loadFromDatabase(intval($id));
            $page = new CategoryPage();
            $page->showCategoryIndex($category);
        }
    }

    protected function routePost()
    {
        $id = intval(Request::getVar(2));
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