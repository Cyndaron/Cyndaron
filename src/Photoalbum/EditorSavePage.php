<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    public const TYPE = 'photoalbum';

    protected function prepare(RequestParameters $post)
    {
        $photoalbum = new Photoalbum($this->id);
        $photoalbum->loadIfIdIsSet();
        $photoalbum->name = $post->getHTML('titel');
        $photoalbum->image = $post->getUrl('image');
        $photoalbum->previewImage = $post->getUrl('previewImage');
        $photoalbum->blurb = $post->getHTML('blurb');
        $photoalbum->notes = $this->parseTextForInlineImages($post->getHTML('artikel'));
        $photoalbum->showBreadcrumbs = $post->getBool('showBreadcrumbs');
        $photoalbum->hideFromOverview = $post->getBool('hideFromOverview');
        $photoalbum->viewMode = $post->getInt('viewMode');
        $this->saveCategories($photoalbum, $post);
        $photoalbum->save();

        User::addNotification('Fotoalbum bewerkt.');
        $this->returnUrl = '/photoalbum/' . $photoalbum->id;
    }
}
