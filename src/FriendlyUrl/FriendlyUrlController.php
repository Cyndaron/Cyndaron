<?php
declare (strict_types = 1);

namespace Cyndaron\FriendlyUrl;

use Cyndaron\Controller;
use Cyndaron\Menu\MenuModel;
use Cyndaron\Request;
use Cyndaron\Url;

class FriendlyUrlController extends Controller
{
    public function routePost()
    {
        try
        {
            switch ($this->action)
            {
                case 'add':
                    $name = Request::post('name');
                    $target = new Url(Request::post('target'));
                    $target->createFriendly($name);
                    break;
                case 'delete':
                    $name = Request::getVar(2);
                    Url::deleteFriendlyUrl($name);
                    break;
                case 'addtomenu':
                    $name = Request::getVar(3);
                    MenuModel::voegToeAanMenu('/' . $name);
                    break;
            }
        }
        catch (\Exception $e)
        {
            $this->send500($e->getMessage());
        }
    }
}