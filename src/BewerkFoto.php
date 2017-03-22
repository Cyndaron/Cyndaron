<?php
namespace Cyndaron;

require_once __DIR__ . '/../functies.gebruikers.php';
require_once __DIR__ . '/../functies.pagina.php';

class BewerkFoto extends Bewerk
{
    protected function prepare()
    {
        $this->type = 'foto';
        $actie = geefGetVeilig('actie');

        if ($actie == 'bewerken')
        {
            $bijschrift = geefPostOnveilig('artikel');

            maakBijschrift($this->id, $bijschrift);
            nieuweMelding('Bijschrift bewerkt.');
        }
    }
}
