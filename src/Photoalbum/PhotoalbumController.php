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
        $id = (int)Request::getVar(1);
        if ($id < 1)
        {
            header('Location: /error/404');
            die('Incorrecte parameter ontvangen.');
        }
        $album = Photoalbum::loadFromDatabase($id);
        $page = new PhotoalbumPage($album);
        $page->render();
    }

    protected function routePost()
    {
        $id = (int)Request::getVar(2);

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
            case 'addPhoto':
                $album = Photoalbum::loadFromDatabase($id);
                Photo::create($album);
                header("Location: /photoalbum/{$album->id}");
                die();
            case 'deletePhoto':
                $album = Photoalbum::loadFromDatabase($id);
                $filename = Request::getVar(3);
                Photo::deleteByAlbumAndFilename($album, $filename);
                header("Location: /photoalbum/{$album->id}");
                die();
        }
    }
}