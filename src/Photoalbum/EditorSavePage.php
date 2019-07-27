<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    const TYPE = 'photoalbum';

    protected function prepare()
    {
        $photoalbum = new Photoalbum($this->id);
        $photoalbum->loadIfIdIsSet();
        $photoalbum->name = Request::unsafePost('titel');
        $photoalbum->notes = Request::unsafePost('artikel');
        $photoalbum->categoryId = (int)Request::post('categoryId');
        $photoalbum->showBreadcrumbs = (bool)Request::post('showBreadcrumbs');
        $photoalbum->hideFromOverview = (bool)Request::post('hideFromOverview');
        $photoalbum->viewMode = (int)Request::post('viewMode');
        $photoalbum->save();

        User::addNotification('Fotoalbum bewerkt.');
        $this->returnUrl = '/photoalbum/' . $photoalbum->id;
    }
}
