<?php
declare (strict_types = 1);

namespace Cyndaron\FriendlyUrl;

use Cyndaron\Controller;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Request;
use Cyndaron\Response\JSONResponse;
use Cyndaron\Url;
use Cyndaron\User\UserLevel;

class FriendlyUrlController extends Controller
{
    protected array $apiPostRoutes = [
        'add' => ['level' => UserLevel::ADMIN, 'function' => 'add'],
        'addtomenu' => ['level' => UserLevel::ADMIN, 'function' => 'addToMenu'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
    ];

    public function add(): JSONResponse
    {
        $name = Request::post('name');
        $target = new Url(Request::post('target'));
        $target->createFriendly($name);

        return new JSONResponse();
    }

    public function addToMenu(): JSONResponse
    {
        $name = Request::getVar(3);
        $menuItem = new MenuItem();
        $menuItem->link = '/' . $name;
        $menuItem->save();

        return new JSONResponse();
    }

    public function delete(): JSONResponse
    {
        $name = Request::getVar(2);
        Url::deleteFriendlyUrl($name);

        return new JSONResponse();
    }
}