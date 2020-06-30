<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;

class EditorSavePagePhoto extends \Cyndaron\Editor\EditorSavePage
{
    public const TYPE = 'photo';

    protected function prepare(RequestParameters $post): void
    {
        $hash = $post->getAlphaNum('hash');
        $caption = $this->parseTextForInlineImages($post->getHTML('artikel'));

        PhotoalbumCaption::create($hash, $caption);
        User::addNotification('Bijschrift bewerkt.');
        $this->returnUrl = $_SESSION['referrer'];
    }
}
