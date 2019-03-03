<?php
declare (strict_types = 1);

namespace Cyndaron\Category;

use Cyndaron\Controller;
use Cyndaron\Menu\MenuModel;
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

        if ($this->action == 'add')
        {
            $name = Request::post('name');
            CategoryModel::nieuweCategorie($name);
        }
        elseif ($this->action == 'edit')
        {
            $name = Request::post('name');
            CategoryModel::wijzigCategorie($id, $name);
        }
        elseif ($this->action == 'delete')
        {
            CategoryModel::verwijderCategorie($id);
        }
        elseif ($this->action == 'addtomenu')
        {
            MenuModel::voegToeAanMenu('/category/' . $id);
        }
        echo json_encode([]);
    }
}