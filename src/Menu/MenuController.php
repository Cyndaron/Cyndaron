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
                $link = Request::geefPostVeilig('link');
                MenuModel::voegToeAanMenu($link);
                break;
            case 'removeItem':
                MenuModel::removeItem($index);
                break;
            case 'setDropdown':
                MenuModel::setProperty($index, 'isDropdown', Request::geefPostVeilig('isDropdown') == 'true' ? 1 : 0);
                break;
            case 'setImage':
                MenuModel::setProperty($index, 'isImage', Request::geefPostVeilig('isImage') == 'true' ? 1 : 0);
                break;
        }
    }
}