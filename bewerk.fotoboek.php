<?php
require_once('check.php');
require_once('functies.pagina.php');
require_once('functies.gebruikers.php');

$actie = geefGetVeilig('actie');

if ($actie == 'bewerken')
{
    $id = geefGetVeilig('id');
    $naam = geefPostOnveilig('titel');
    $notities = geefPostOnveilig('artikel');

    if ($id > 0) // Als het id is meegegeven bestond de categorie al.
    {
        wijzigFotoalbum($id, $naam, $notities);
    }
    else
    {
        $id = nieuwFotoalbum($naam, $notities);
    }

    nieuweMelding('Fotoboek bewerkt.');
    $returnUrl = 'toonfotoboek.php?id=' . $id;
}
