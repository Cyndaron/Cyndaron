<?php
declare (strict_types = 1);

namespace Cyndaron\StaticPage;

use Cyndaron\Controller;
use Cyndaron\Menu\Menu;
use Cyndaron\Request;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;

class StaticPageController extends Controller
{
    protected $minLevelPost = UserLevel::ANONYMOUS;

    protected function routeGet()
    {
        $id = intval(Request::getVar(1));
        new StaticPage($id);
    }

    protected function routePost()
    {
        $id = intval(Request::getVar(2));

        if ($this->action !== 'react' && !User::isAdmin())
        {
            $this->send403();
            die();
        }

        switch ($this->action)
        {
            case 'react':
                $this->react($id);
                break;
            case 'delete':
                $model = new StaticPageModel($id);
                $model->delete();
                break;
            case 'addtomenu':
                Menu::addItem('/sub/' . $id);
                break;
        }
    }

    private function react(int $id)
    {
        $model = new StaticPageModel($id);
        if (!$model->laden())
        {
            header('Location: /error/404');
            die('Pagina bestaat niet.');
        }

        $auteur = Request::post('auteur');
        $reactie = Request::post('reactie');
        $antispam = strtolower(Request::post('antispam'));
        $model->react($auteur, $reactie, $antispam);

        header('Location: /sub/' . $id);
    }
}