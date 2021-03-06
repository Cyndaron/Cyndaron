<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;

final class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    public const TYPE = 'photoalbum';

    protected function prepare(RequestParameters $post): void
    {
        $photoalbum = new Photoalbum($this->id);
        $photoalbum->loadIfIdIsSet();
        $photoalbum->name = $post->getHTML('titel');
        $photoalbum->blurb = $post->getHTML('blurb');
        $photoalbum->notes = $this->parseTextForInlineImages($post->getHTML('artikel'));
        $photoalbum->showBreadcrumbs = $post->getBool('showBreadcrumbs');
        $photoalbum->hideFromOverview = $post->getBool('hideFromOverview');
        $photoalbum->viewMode = $post->getInt('viewMode');
        $this->saveHeaderAndPreviewImage($photoalbum, $post);
        $photoalbum->save();
        $this->saveCategories($photoalbum, $post);

        User::addNotification('Fotoalbum bewerkt.');
        $this->returnUrl = '/photoalbum/' . $photoalbum->id;
    }
}
