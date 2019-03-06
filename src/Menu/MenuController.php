<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Controller;
use Cyndaron\Request;

class MenuController extends Controller
{
    public function routePost()
    {
        $index = intval(Request::getVar(2));
        switch ($this->action)
        {
            case 'addItem':
                $link = Request::post('link');
                Menu::addItem($link);
                break;
            case 'removeItem':
                Menu::removeItem($index);
                break;
            case 'setDropdown':
                Menu::setProperty($index, 'isDropdown', Request::post('isDropdown') == 'true' ? 1 : 0);
                break;
            case 'setImage':
                Menu::setProperty($index, 'isImage', Request::post('isImage') == 'true' ? 1 : 0);
                break;
        }
    }
}