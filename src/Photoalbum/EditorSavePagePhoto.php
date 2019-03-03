<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePagePhoto extends \Cyndaron\Editor\EditorSavePage
{
    protected $type = 'photo';

    protected function prepare()
    {
        $hash = Request::getVar(3);
        $caption = Request::geefPostOnveilig('artikel');

        PhotoModel::addCaption($hash, $caption);
        User::addNotification('Bijschrift bewerkt.');
    }
}
