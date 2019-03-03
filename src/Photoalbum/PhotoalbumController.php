<?php
declare (strict_types = 1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Controller;
use Cyndaron\Menu\MenuModel;
use Cyndaron\Request;

class PhotoalbumController extends Controller
{
    public function routeGet()
    {
        $id = intval(Request::getVar(1));
        new PhotoalbumPage($id);
    }

    public function routePost()
    {
        $id = intval(Request::getVar(2));

        switch ($this->action)
        {
            case 'add':
                $name = Request::post('name');
                PhotoalbumModel::nieuwFotoalbum($name);
                break;
            case 'edit':
                $name = Request::post('name');
                PhotoalbumModel::wijzigFotoalbum($id, $name);
                break;
            case 'delete':
                PhotoalbumModel::verwijderFotoalbum($id);
                break;
            case 'addtomenu':
                MenuModel::voegToeAanMenu('/photoalbum/' . $id);
                break;
        }
    }
}