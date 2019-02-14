<?php
namespace Cyndaron;

use Cyndaron\StaticPage\StaticPageModel;
use Cyndaron\User\User;

class BewerkStatischePagina extends Bewerk
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
