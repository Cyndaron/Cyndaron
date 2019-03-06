<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Request;

class MenuController extends Controller
{
    public function routePost()
    {
        $index = intval(Request::getVar(2));
        switch ($this->action)
        {
            case 'addItem':
                $volgorde = (int)Request::post('volgorde');
                $link = Request::post('link');
                $alias = Request::post('alias');
                $isDropdown = (bool)Request::post('isDropdown');
                $isImage = (bool)Request::post('isImage');
                if (!Menu::addItem($link, $alias, $volgorde, $isDropdown, $isImage))
                {
                    var_dump(DBConnection::errorInfo());
                    throw new \Exception('Cannot add menu item!');
                }

                break;
            case 'editItem':
                $editArray = [
                    'volgorde' => Request::post('volgorde'),
                    'link' => Request::post('link'),
                    'alias' => Request::post('alias'),
                    'isDropdown' => (int)Request::post('isDropdown'),
                    'isImage' => (int)Request::post('isImage'),
                ];
                Menu::editItem($index, $editArray);
                break;
            case 'deleteItem':
                Menu::deleteItem($index);
                break;
        }
    }
}