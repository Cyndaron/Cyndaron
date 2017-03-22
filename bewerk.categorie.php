<?php
require_once('check.php');
require_once('functies.pagina.php');
require_once('functies.gebruikers.php');

$actie = geefGetVeilig('actie');

if ($actie == 'bewerken')
{
    $id = geefGetVeilig('id');
    $titel = geefPostOnveilig('titel');
    $beschrijving = parseTextForInlineImages(geefPostOnveilig('artikel'));
    $alleentitel = parseCheckBoxAlsBool(geefPostOnveilig('alleentitel'));

    if ($id > 0) // Als het id is meegegeven bestond de categorie al.
    {
        wijzigCategorie($id, $titel, $alleentitel, $beschrijving);
    }
    else
    {
        $id = nieuweCategorie($titel, $alleentitel, $beschrijving);
    }

    nieuweMelding('Categorie bewerkt.');
    $returnUrl = 'tooncategorie.php?id=' . $id;
}
