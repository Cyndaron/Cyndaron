<?php
declare (strict_types = 1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Controller;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Request;
use Cyndaron\Response\JSONResponse;
use Cyndaron\User\UserLevel;

class PhotoalbumController extends Controller
{
    protected array $apiPostRoutes = [
        'add' => ['level' => UserLevel::ADMIN, 'function' => 'add'],
        'addPhoto' => ['level' => UserLevel::ADMIN, 'function' => 'addPhoto'],
        'addtomenu' => ['level' => UserLevel::ADMIN, 'function' => 'addToMenu'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'deletePhoto' => ['level' => UserLevel::ADMIN, 'function' => 'deletePhoto'],
        'edit' => ['level' => UserLevel::ADMIN, 'function' => 'edit'],
    ];

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
        $page->renderAndEcho();
    }

    public function add(): JSONResponse
    {
        $name = Request::post('name');
        Photoalbum::create($name);

        return new JSONResponse();
    }

    public function addPhoto(): JSONResponse
    {
        $id = (int)Request::getVar(2);

        $album = Photoalbum::loadFromDatabase($id);
        Photo::create($album);
        header("Location: /photoalbum/{$album->id}");
        die();
    }

    public function addToMenu(): JSONResponse
    {
        $id = (int)Request::getVar(2);

        $menuItem = new MenuItem();
        $menuItem->link = '/photoalbum/' . $id;
        $menuItem->save();

        return new JSONResponse();
    }

    public function delete(): JSONResponse
    {
        $id = (int)Request::getVar(2);

        $obj = new Photoalbum($id);
        $obj->delete();

        return new JSONResponse();
    }

    public function deletePhoto(): JSONResponse
    {
        $id = (int)Request::getVar(2);

        $album = Photoalbum::loadFromDatabase($id);
        $filename = Request::getVar(3);
        Photo::deleteByAlbumAndFilename($album, $filename);
        header("Location: /photoalbum/{$album->id}");
        die();

    }

    public function edit(): JSONResponse
    {
        $id = (int)Request::getVar(2);

        $name = Request::post('name');
        Photoalbum::edit($id, $name);

        return new JSONResponse();
    }
}