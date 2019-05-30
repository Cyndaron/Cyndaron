<?php
declare (strict_types = 1);

namespace Cyndaron\StaticPage;

use Cyndaron\Controller;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;

class StaticPageController extends Controller
{
    protected $postRoutes = [
        'addtomenu' => ['level' => UserLevel::ADMIN, 'addToMenu'],
        'delete' => ['level' => UserLevel::ADMIN, 'delete'],
        'react' => ['level' => UserLevel::ANONYMOUS, 'react'],
    ];

    protected function routeGet()
    {
        $id = intval(Request::getVar(1));
        new StaticPage($id);
    }

    protected function addToMenu()
    {
        $id = intval(Request::getVar(2));
        $menuItem = new MenuItem();
        $menuItem->link = '/sub/' . $id;
        $menuItem->save();
    }

    protected function delete()
    {
        $id = intval(Request::getVar(2));
        $model = new StaticPageModel($id);
        $model->delete();
    }

    protected function react()
    {
        $id = intval(Request::getVar(2));
        $model = new StaticPageModel($id);
        if (!$model->load())
        {
            header('Location: /error/404');
            die('Pagina bestaat niet.');
        }

        $author = Request::post('author');
        $reactie = Request::post('reactie');
        $antispam = strtolower(Request::post('antispam'));
        $model->react($author, $reactie, $antispam);

        header('Location: /sub/' . $id);
    }
}