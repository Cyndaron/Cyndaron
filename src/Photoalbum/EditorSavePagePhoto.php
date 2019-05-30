<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePagePhoto extends \Cyndaron\Editor\EditorSavePage
{
    const TYPE = 'photo';

    protected function prepare()
    {
        $hash = Request::getVar(3);
        $caption = Request::unsafePost('artikel');

        PhotoModel::addCaption($hash, $caption);
        User::addNotification('Bijschrift bewerkt.');
    }
}
