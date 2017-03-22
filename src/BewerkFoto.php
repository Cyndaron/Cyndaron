<?php
namespace Cyndaron;

require_once __DIR__ . '/../functies.pagina.php';

class BewerkFoto extends Bewerk
{
    protected function prepare()
    {
        $this->type = 'foto';
        $actie = Request::geefGetVeilig('actie');

        if ($actie == 'bewerken')
        {
            $bijschrift = Request::geefPostOnveilig('artikel');

            maakBijschrift($this->id, $bijschrift);
            Gebruiker::nieuweMelding('Bijschrift bewerkt.');
        }
    }
}
