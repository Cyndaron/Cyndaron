<?php
declare(strict_types = 1);

namespace Cyndaron\PageManager;

use Cyndaron\Category\CategoryModel;
use Cyndaron\Controller;
use Cyndaron\FotoalbumModel;
use Cyndaron\Menu\MenuModel;
use Cyndaron\Request;
use Cyndaron\StatischePaginaModel;
use Cyndaron\Url;
use Cyndaron\User\UserLevel;

class PageManagerController extends Controller
{
    protected $minLevelGet = UserLevel::ADMIN;

    public function routeGet()
    {
        new PageManagerPage();
    }

    public function routePost()
    {
        try
        {
            $type = Request::getVar(1);
            $action = Request::getVar(2);
            $id = intval(Request::getVar(3));

            if ($type == 'photoalbum')
            {
                if ($action == 'add')
                {
                    $name = Request::geefPostVeilig('name');
                    FotoalbumModel::nieuwFotoalbum($name);
                }
                elseif ($action == 'edit')
                {
                    $name = Request::geefPostVeilig('name');
                    FotoalbumModel::wijzigFotoalbum($id, $name);
                }
                elseif ($action == 'delete')
                {
                    FotoalbumModel::verwijderFotoalbum($id);
                }
                elseif ($action == 'addtomenu')
                {
                    MenuModel::voegToeAanMenu('toonfotoboek.php?id=' . $id);
                }
            }
            elseif ($type == 'sub')
            {
                if ($action == 'delete')
                {
                    $model = new StatischePaginaModel($id);
                    $model->verwijder();
                }
                elseif ($action == 'addtomenu')
                {
                    MenuModel::voegToeAanMenu('toonsub.php?id=' . $id);
                }

            }
            elseif ($type == 'friendlyurl')
            {
                if ($action == 'add')
                {
                    $name = Request::geefPostVeilig('name');
                    $target = new Url(Request::geefPostVeilig('target'));
                    $target->maakFriendly($name);
                }
                elseif ($action == 'delete')
                {
                    $name = Request::getVar(3);
                    Url::verwijderFriendlyUrl($name);
                }
                elseif ($action == 'addtomenu')
                {
                    $name = Request::getVar(3);
                    MenuModel::voegToeAanMenu($name);
                }
            }
            echo json_encode([]);
        }
        catch (\Exception $e)
        {
            $this->send500($e->getMessage());
        }
    }
}