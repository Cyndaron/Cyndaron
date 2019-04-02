<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    protected $type = 'photoalbum';

    protected function prepare()
    {
        $naam = Request::unsafePost('titel');
        $notities = Request::unsafePost('artikel');
        $showBreadcrumbs = (bool)Request::post('showBreadcrumbs');

        if ($this->id > 0) // Als het id is meegegeven bestond de categorie al.
        {
            Photoalbum::wijzigFotoalbum($this->id, $naam, $notities, $showBreadcrumbs);
        }
        else
        {
            $this->id = Photoalbum::nieuwFotoalbum($naam, $notities, $showBreadcrumbs);
        }

        User::addNotification('Fotoalbum bewerkt.');
        $this->returnUrl = '/photoalbum/' . $this->id;
    }
}
