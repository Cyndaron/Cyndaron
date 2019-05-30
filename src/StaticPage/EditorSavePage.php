<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    const TYPE = 'sub';

    protected function prepare()
    {
        $titel = Request::unsafePost('titel');
        $text = $this->parseTextForInlineImages(Request::unsafePost('artikel'));
        $enableComments = (bool)Request::post('enableComments');
        $showBreadcrumbs = (bool)Request::post('showBreadcrumbs');
        $categoryId = intval(Request::post('categoryId'));

        $model = new StaticPageModel($this->id);
        $model->loadIfIdIsSet();
        $model->name = $titel;
        $model->text = $text;
        $model->enableComments = $enableComments;
        $model->showBreadcrumbs = $showBreadcrumbs;
        $model->categoryId = $categoryId;

        if ($model->save())
        {
            $this->id = $model->id;

            User::addNotification('Pagina bewerkt.');
            $this->returnUrl = '/sub/' . $this->id;
        }
        else
        {
            $error = var_export(DBConnection::errorInfo(), true);
            User::addNotification('Pagine opslaan mislukt: ' . $error);
        }
    }
}
