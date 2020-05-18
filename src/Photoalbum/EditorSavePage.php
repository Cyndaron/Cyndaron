<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    const TYPE = 'photoalbum';

    protected function prepare(RequestParameters $post)
    {
        $photoalbum = new Photoalbum($this->id);
        $photoalbum->loadIfIdIsSet();
        $photoalbum->name = $post->getHTML('titel');
        $photoalbum->notes = $this->parseTextForInlineImages($post->getHTML('artikel'));
        $photoalbum->categoryId = $post->getInt('categoryId');
        $photoalbum->showBreadcrumbs = $post->getBool('showBreadcrumbs');
        $photoalbum->hideFromOverview = $post->getBool('hideFromOverview');
        $photoalbum->viewMode = $post->getInt('viewMode');
        $photoalbum->save();

        User::addNotification('Fotoalbum bewerkt.');
        $this->returnUrl = '/photoalbum/' . $photoalbum->id;
    }
}
