<?php
namespace Cyndaron\StaticPage;

use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    protected $type = 'sub';

    protected function prepare()
    {
        $titel = Request::geefPostOnveilig('titel');
        $tekst = $this->parseTextForInlineImages(Request::geefPostOnveilig('artikel'));
        $reacties_aan = Request::geefPostVeilig('reacties_aan');
        $categorieid = intval(Request::geefPostVeilig('categorieid'));

        $model = new StaticPageModel($this->id);
        $model->setName($titel);
        $model->setText($tekst);
        $model->setEnableComments($reacties_aan);
        $model->setCategoryId($categorieid);
        $model->opslaan();
        $this->id = $model->getId();

        User::addNotification('Pagina bewerkt.');
        $this->returnUrl = '/sub/' . $this->id;
    }
}
