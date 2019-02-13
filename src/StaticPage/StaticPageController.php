<?php
declare (strict_types = 1);
namespace Cyndaron\StaticPage;

use Cyndaron\Controller;
use Cyndaron\Menu\MenuModel;
use Cyndaron\Request;

class StaticPageController extends Controller
{
    public function routeGet()
    {
        $id = intval(Request::getVar(1));
        new StaticPage($id);
    }

    public function routePost()
    {
        $id = intval(Request::getVar(2));

        switch ($this->action)
        {
            case 'delete':
                $model = new StaticPageModel($id);
                $model->verwijder();
                break;
            case 'addtomenu':
                MenuModel::voegToeAanMenu('/sub/' . $id);
                break;
        }
    }
}