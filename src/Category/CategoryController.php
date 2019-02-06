<?php
declare(strict_types = 1);

namespace Cyndaron\Category;

use Cyndaron\Controller;
use Cyndaron\Menu\MenuModel;
use Cyndaron\Request;
use Cyndaron\User\User;

class CategoryController extends Controller
{
    public function route()
    {
        if (!User::isAdmin())
        {
            $this->send401();
            return;
        }

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
                MenuModel::voegToeAanMenu('tooncategorie.php?id=' . $id);
            }
            echo json_encode([]);
        }
        catch (\Exception $e)
        {
            $this->send500($e->getMessage());
        }
    }
}