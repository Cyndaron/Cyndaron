<?php
declare(strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Request;

require_once __DIR__ . '/../../check.php';

class MenuEditorController
{
    public function __construct()
    {
        if (!Request::postIsLeeg())
        {
            $action = Request::geefPostVeilig('action');

            switch ($action)
            {
                case 'removeItem':
                    $index = intval(Request::geefPostVeilig('index'));
                    MenuModel::removeItem($index);
                case 'setDropdown':
                    $index = intval(Request::geefPostVeilig('index'));
                    MenuModel::setProperty($index, 'isDropdown', Request::geefPostVeilig('isDropdown') == "true" ? 1 : 0);
                    break;
                case 'setImage':
                    $index = intval(Request::geefPostVeilig('index'));
                    MenuModel::setProperty($index, 'isImage', Request::geefPostVeilig('isImage') == "true" ? 1 : 0);
                    break;
            }
        }
        else
        {
            new MenuEditorPage();
        }
    }
}