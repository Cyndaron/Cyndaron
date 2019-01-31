<?php
namespace Cyndaron;


use Cyndaron\User\User;

class BewerkFotoalbum extends Bewerk
{
    protected function prepare()
    {
        $this->type = 'fotoboek';
        $actie = Request::geefGetVeilig('actie');

        if ($actie == 'bewerken')
        {
            $naam = Request::geefPostOnveilig('titel');
            $notities = Request::geefPostOnveilig('artikel');

            if ($this->id > 0) // Als het id is meegegeven bestond de categorie al.
            {
                FotoalbumModel::wijzigFotoalbum($this->id, $naam, $notities);
            }
            else
            {
                $this->id = FotoalbumModel::nieuwFotoalbum($naam, $notities);
            }

            User::addNotification('Fotoboek bewerkt.');
            $this->returnUrl = '/toonfotoboek.php?id=' . $this->id;
        }
    }
}
