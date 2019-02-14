<?php
namespace Cyndaron;

use Cyndaron\User\User;

class BewerkFoto extends Bewerk
{
    protected $type = 'photo';

    protected function prepare()
    {
        $hash = Request::getVar(3);
        $bijschrift = Request::geefPostOnveilig('artikel');

        FotoModel::maakBijschrift($hash, $bijschrift);
        User::addNotification('Bijschrift bewerkt.');
    }
}
