<?php
declare (strict_types = 1);

namespace Cyndaron\Category;

use Cyndaron\Controller;
use Cyndaron\Menu\Menu;
use Cyndaron\Request;

class CategoryController extends Controller
{
    public function routeGet()
    {
        $id = Request::getVar(1);
        new CategoryPage($id);
    }

    public function routePost()
    {
        $id = intval(Request::getVar(2));

        switch ($this->action)
        {
            case 'add':
                $name = Request::post('name');
                Category::create($name);
                break;
            case 'edit':
                $name = Request::post('name');
                Category::edit($id, $name);
                break;
            case 'delete':
                $obj = new Category($id);
                $obj->delete();
                break;
            case 'addtomenu':
                Menu::addItem('/category/' . $id);
                break;
        }

        echo json_encode([]);
    }
}