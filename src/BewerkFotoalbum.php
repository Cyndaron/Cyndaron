<?php
namespace Cyndaron;

require_once __DIR__ . '/../functies.gebruikers.php';
require_once __DIR__ . '/../functies.pagina.php';

class BewerkFotoalbum extends Bewerk
{
    protected function prepare()
    {
        $this->type = 'fotoboek';
        $actie = geefGetVeilig('actie');

        if ($actie == 'bewerken')
        {
            $naam = geefPostOnveilig('titel');
            $notities = geefPostOnveilig('artikel');

            if ($this->id > 0) // Als het id is meegegeven bestond de categorie al.
            {
                wijzigFotoalbum($this->id, $naam, $notities);
            }
            else
            {
                $this->id = nieuwFotoalbum($naam, $notities);
            }

            nieuweMelding('Fotoboek bewerkt.');
            $this->returnUrl = 'toonfotoboek.php?id=' . $this->id;
        }
    }
}
