<?php
declare (strict_types = 1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Controller;
use Cyndaron\Menu\Menu;
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
                Photoalbum::nieuwFotoalbum($name);
                break;
            case 'edit':
                $name = Request::post('name');
                Photoalbum::wijzigFotoalbum($id, $name);
                break;
            case 'delete':
                $obj = new Photoalbum($id);
                $obj->delete();
                break;
            case 'addtomenu':
                Menu::addItem('/photoalbum/' . $id);
                break;
        }
    }
}