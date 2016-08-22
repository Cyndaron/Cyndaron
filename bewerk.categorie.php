<?php
require_once('check.php');
require_once('functies.pagina.php');
require_once('functies.gebruikers.php');
require_once('pagina.php');

$actie = $_GET['actie'];

if ($actie == 'bewerken')
{
    $id = $_GET['id'];
    $titel = $_POST['titel'];
    $beschrijving = $_POST['artikel'];
    $alleentitel = parseCheckBoxAlsBool($_POST['alleentitel']);

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
