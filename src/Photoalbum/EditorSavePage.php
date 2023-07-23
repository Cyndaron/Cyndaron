<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Symfony\Component\HttpFoundation\Request;

final class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    public const TYPE = 'photoalbum';

    protected function prepare(RequestParameters $post, Request $request): void
    {
        $photoalbum = new Photoalbum($this->id);
        $photoalbum->loadIfIdIsSet();
        $photoalbum->name = $post->getHTML('titel');
        $photoalbum->blurb = $post->getHTML('blurb');
        $photoalbum->notes = $this->parseTextForInlineImages($post->getHTML('artikel'));
        $photoalbum->showBreadcrumbs = $post->getBool('showBreadcrumbs');
        $photoalbum->hideFromOverview = $post->getBool('hideFromOverview');
        $photoalbum->viewMode = $post->getInt('viewMode');
        $photoalbum->thumbnailWidth = $post->getInt('thumbnailWidth');
        $photoalbum->thumbnailHeight = $post->getInt('thumbnailHeight');
        $this->saveHeaderAndPreviewImage($photoalbum, $post, $request);
        $photoalbum->save();
        $this->saveCategories($photoalbum, $post);

        User::addNotification('Fotoalbum bewerkt.');
        $this->returnUrl = '/photoalbum/' . $photoalbum->id;
    }
}
