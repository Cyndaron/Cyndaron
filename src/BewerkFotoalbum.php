<?php
namespace Cyndaron;

require_once __DIR__ . '/../functies.pagina.php';

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
                wijzigFotoalbum($this->id, $naam, $notities);
            }
            else
            {
                $this->id = nieuwFotoalbum($naam, $notities);
            }

            Gebruiker::nieuweMelding('Fotoboek bewerkt.');
            $this->returnUrl = 'toonfotoboek.php?id=' . $this->id;
        }
    }
}
