<?php
declare(strict_types = 1);

namespace Cyndaron\PageManager;

use Cyndaron\CategorieModel;
use Cyndaron\FotoalbumModel;
use Cyndaron\Menu\MenuModel;
use Cyndaron\Request;
use Cyndaron\StatischePaginaModel;
use Cyndaron\Url;

require_once __DIR__ . '/../../check.php';

class PageManagerController
{
    public function __construct()
    {
        $action = Request::getVar(2);

        if ($action !== null)
        {
            $this->handlePost();
        }
        else
        {
            new PageManagerPage();

        }
    }

    private function handlePost()
    {
        try
        {
            $type = Request::getVar(1);
            $action = Request::getVar(2);
            $id = intval(Request::getVar(3));

            if ($type == 'category')
            {
                if ($action == 'new')
                {
                    $name = Request::geefPostVeilig('name');
                    CategorieModel::nieuweCategorie($name);
                }
                elseif ($action == 'edit')
                {
                    $name = Request::geefPostVeilig('name');
                    CategorieModel::wijzigCategorie($id, $name);
                }
                elseif ($action == 'delete')
                {
                    CategorieModel::verwijderCategorie($id);
                }
                elseif ($action == 'addtomenu')
                {
                    MenuModel::voegToeAanMenu('tooncategorie.php?id=' . $id);
                }
            }
            elseif ($type == 'photoalbum')
            {
                if ($action == 'new')
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
                if ($action == 'new')
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
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode([
                'error' => $e->getMessage()
            ]);
        }
    }
}