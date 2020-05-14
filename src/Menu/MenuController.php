<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class MenuController extends Controller
{
    protected array $apiPostRoutes = [
        'addItem' => ['level' => UserLevel::ADMIN, 'function' => 'addItem'],
        'editItem' => ['level' => UserLevel::ADMIN, 'function' => 'editItem'],
        'deleteItem' => ['level' => UserLevel::ADMIN, 'function' => 'deleteItem'],
    ];

    protected function addItem(): JsonResponse
    {
        $menuItem = new MenuItem();
        $menuItem->link = Request::post('link');
        $menuItem->alias = Request::post('alias');
        $menuItem->isDropdown = (bool)Request::post('isDropdown');
        $menuItem->isImage = (bool)Request::post('isImage');
        $menuItem->priority = (int)Request::post('priority');

        if (!$menuItem->save())
        {
            throw new Exception('Cannot add menu item!');
        }

        return new JsonResponse();
    }

    protected function editItem(): JsonResponse
    {
        $index = (int)Request::getVar(2);
        $menuItem = new MenuItem($index);
        $menuItem->load();
        $menuItem->link = Request::post('link');
        $menuItem->alias = Request::post('alias');
        $menuItem->isDropdown = (bool)(int)Request::post('isDropdown');
        $menuItem->isImage = (bool)(int)Request::post('isImage');
        $menuItem->priority = (int)Request::post('priority');

        if (!$menuItem->save())
        {
            throw new Exception('Could not edit menu item!');
        }

        return new JsonResponse();
    }

    protected function deleteItem(): JsonResponse
    {
        $id = (int)Request::getVar(2);
        $menuItem = new MenuItem($id);
        $menuItem->delete();

        return new JsonResponse();
    }
}