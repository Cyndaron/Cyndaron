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
        try
        {
            $action = Request::getVar(1);
            $id = intval(Request::getVar(2));

            if ($action == 'add')
            {
                $name = Request::geefPostVeilig('name');
                CategoryModel::nieuweCategorie($name);
            }
            elseif ($action == 'edit')
            {
                $name = Request::geefPostVeilig('name');
                CategoryModel::wijzigCategorie($id, $name);
            }
            elseif ($action == 'delete')
            {
                CategoryModel::verwijderCategorie($id);
            }
            elseif ($action == 'addtomenu')
            {
                MenuModel::voegToeAanMenu('/category/' . $id);
            }
            echo json_encode([]);
        }
        catch (\Exception $e)
        {
            $this->send500($e->getMessage());
        }
    }
}