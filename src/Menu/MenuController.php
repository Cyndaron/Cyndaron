<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;

class MenuController extends Controller
{
    protected $postRoutes = [
        'addItem' => ['level' => UserLevel::ADMIN, 'function' => 'addItem'],
        'editItem' => ['level' => UserLevel::ADMIN, 'function' => 'editItem'],
        'deleteItem' => ['level' => UserLevel::ADMIN, 'function' => 'deleteItem'],
    ];

    protected function addItem()
    {
        $volgorde = (int)Request::post('volgorde');
        $link = Request::post('link');
        $alias = Request::post('alias');
        $isDropdown = (bool)Request::post('isDropdown');
        $isImage = (bool)Request::post('isImage');
        if (!Menu::addItem($link, $alias, $volgorde, $isDropdown, $isImage))
        {
            throw new \Exception('Cannot add menu item!');
        }
    }

    protected function editItem()
    {
        $index = intval(Request::getVar(2));
        $editArray = [
            'volgorde' => Request::post('volgorde'),
            'link' => Request::post('link'),
            'alias' => Request::post('alias'),
            'isDropdown' => (int)Request::post('isDropdown'),
            'isImage' => (int)Request::post('isImage'),
        ];
        Menu::editItem($index, $editArray);
    }

    protected function deleteItem()
    {
        $index = intval(Request::getVar(2));
        Menu::deleteItem($index);
    }
}