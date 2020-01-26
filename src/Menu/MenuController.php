<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Exception;

class MenuController extends Controller
{
    protected array $postRoutes = [
        'addItem' => ['level' => UserLevel::ADMIN, 'function' => 'addItem'],
        'editItem' => ['level' => UserLevel::ADMIN, 'function' => 'editItem'],
        'deleteItem' => ['level' => UserLevel::ADMIN, 'function' => 'deleteItem'],
    ];

    protected function addItem()
    {
        $menuItem = new MenuItem();
        $menuItem->link = Request::post('link');
        $menuItem->alias = Request::post('alias');
        $menuItem->isDropdown = (bool)Request::post('isDropdown');
        $menuItem->isImage = (bool)Request::post('isImage');
        $menuItem->priority = Request::post('priority');

        if (!$menuItem->save())
        {
            throw new Exception('Cannot add menu item!');
        }
    }

    protected function editItem()
    {
        $index = intval(Request::getVar(2));
        $menuItem = new MenuItem($index);
        $menuItem->load();
        $menuItem->link = Request::post('link');
        $menuItem->alias = Request::post('alias');
        $menuItem->isDropdown = (bool)(int)Request::post('isDropdown');
        $menuItem->isImage = (bool)(int)Request::post('isImage');
        $menuItem->priority = Request::post('priority');

        if (!$menuItem->save())
        {
            throw new Exception('Could not edit menu item!');
        }
    }

    protected function deleteItem()
    {
        $id = intval(Request::getVar(2));
        $menuItem = new MenuItem($id);
        $menuItem->delete();
    }
}