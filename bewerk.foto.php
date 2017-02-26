<?php
require('check.php');
require_once('functies.pagina.php');
require_once('functies.gebruikers.php');
require_once('pagina.php');

$actie = geefGetVeilig('actie');

if ($actie == 'bewerken')
{
    $hash = geefGetVeilig('id');
    $bijschrift = geefPostOnveilig('artikel');

    maakBijschrift($hash, $bijschrift);
    nieuweMelding('Bijschrift bewerkt.');
}
