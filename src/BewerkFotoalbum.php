<?php
namespace Cyndaron;

use Cyndaron\User\User;

class BewerkFotoalbum extends Bewerk
{
    protected $type = 'photoalbum';

    protected function prepare()
    {
        $naam = Request::geefPostOnveilig('titel');
        $notities = Request::geefPostOnveilig('artikel');

        if ($this->id > 0) // Als het id is meegegeven bestond de categorie al.
        {
            PhotoalbumModel::wijzigFotoalbum($this->id, $naam, $notities);
        }
        else
        {
            $this->id = PhotoalbumModel::nieuwFotoalbum($naam, $notities);
        }

        User::addNotification('Fotoalbum bewerkt.');
        $this->returnUrl = '/photoalbum/' . $this->id;
    }
}
