<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePagePhoto extends \Cyndaron\Editor\EditorSavePage
{
    const TYPE = 'photo';

    protected function prepare()
    {
        $hash = Request::post('hash');
        $caption = Request::unsafePost('artikel');

        PhotoalbumCaption::create($hash, $caption);
        User::addNotification('Bijschrift bewerkt.');
        $this->returnUrl = $_SESSION['referrer'];
    }
}
