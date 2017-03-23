<?php
namespace Cyndaron;


class BewerkFoto extends Bewerk
{
    protected function prepare()
    {
        $this->type = 'foto';
        $actie = Request::geefGetVeilig('actie');
        $hash = Request::geefGetVeilig('hash');

        if ($actie == 'bewerken')
        {
            $bijschrift = Request::geefPostOnveilig('artikel');

            FotoModel::maakBijschrift($hash, $bijschrift);
            Gebruiker::nieuweMelding('Bijschrift bewerkt.');
        }
    }
}
