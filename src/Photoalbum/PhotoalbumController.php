<?php
declare (strict_types = 1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Controller;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Request;

class PhotoalbumController extends Controller
{
    protected function routeGet()
    {
        $id = intval(Request::getVar(1));
        $page = new PhotoalbumPage($id);
        $page->showPrePage();
        $page->showBody();
        $page->showPostPage();
    }

    protected function routePost()
    {
        $id = intval(Request::getVar(2));

        switch ($this->action)
        {
            case 'add':
                $name = Request::post('name');
                Photoalbum::create($name);
                break;
            case 'edit':
                $name = Request::post('name');
                Photoalbum::edit($id, $name);
                break;
            case 'delete':
                $obj = new Photoalbum($id);
                $obj->delete();
                break;
            case 'addtomenu':
                $menuItem = new MenuItem();
                $menuItem->link = '/photoalbum/' . $id;
                $menuItem->save();
                break;
        }
    }
}