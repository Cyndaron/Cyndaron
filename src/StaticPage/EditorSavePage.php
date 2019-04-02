<?php
namespace Cyndaron\StaticPage;

use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    protected $type = 'sub';

    protected function prepare()
    {
        $titel = Request::unsafePost('titel');
        $tekst = $this->parseTextForInlineImages(Request::unsafePost('artikel'));
        $reacties_aan = Request::post('reacties_aan');
        $showBreadcrumbs = Request::post('showBreadcrumbs');
        $categorieid = intval(Request::post('categorieid'));

        $model = new StaticPageModel($this->id);
        $model->setName($titel);
        $model->setText($tekst);
        $model->setEnableComments($reacties_aan);
        $model->setShowBreadcrumbs($showBreadcrumbs);
        $model->setCategoryId($categorieid);
        $model->opslaan();
        $this->id = $model->getId();

        User::addNotification('Pagina bewerkt.');
        $this->returnUrl = '/sub/' . $this->id;
    }
}
