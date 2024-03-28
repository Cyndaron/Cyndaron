<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Symfony\Component\HttpFoundation\Request;

final class EditorSavePagePhoto extends \Cyndaron\Editor\EditorSavePage
{
    public const TYPE = 'photo';

    protected function prepare(RequestParameters $post, Request $request): void
    {
        $photoalbumId = $post->getInt('photoalbumId');
        $hash = $post->getAlphaNum('hash');
        $caption = $this->imageExtractor->process($post->getHTML('artikel'));

        PhotoalbumCaption::create($hash, $caption);
        User::addNotification('Bijschrift bewerkt.');
        $this->returnUrl = '/photoalbum/' . $photoalbumId;
    }
}
