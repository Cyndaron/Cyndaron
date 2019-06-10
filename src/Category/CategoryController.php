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
        new CategoryPage($id);
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