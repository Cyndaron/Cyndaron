<?php
declare (strict_types = 1);

namespace Cyndaron\PageManager;

use Cyndaron\Category\CategoryModel;
use Cyndaron\Controller;
use Cyndaron\PhotoalbumModel;
use Cyndaron\Menu\MenuModel;
use Cyndaron\Request;
use Cyndaron\StaticPage\StaticPageModel;
use Cyndaron\Url;
use Cyndaron\User\UserLevel;

class PageManagerController extends Controller
{
    protected $minLevelGet = UserLevel::ADMIN;

    public function routeGet()
    {
        $currentPage = Request::getVar(1) ?: 'sub';
        new PageManagerPage($currentPage);
    }

    public function routePost()
    {
        try
        {
            $type = Request::getVar(1);
            $action = Request::getVar(2);

            if ($type == 'friendlyurl')
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
                    MenuModel::voegToeAanMenu('/' . $name);
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