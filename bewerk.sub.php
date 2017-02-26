<?php
require_once('check.php');
require_once('functies.pagina.php');
require_once('functies.gebruikers.php');
require_once('pagina.php');

$actie = geefGetVeilig('actie');

if ($actie == 'bewerken')
{
    $id = geefGetVeilig('id');
    $titel = geefPostOnveilig('titel');
    $tekst = parseTextForInlineImages(geefPostOnveilig('artikel'));
    $reacties_aan = geefPostOnveilig('reacties_aan');
    $categorieid = geefPostOnveilig('categorieid');

    if (!$categorieid)
        $categorieid = '0';

    if ($id > 0) // Als het id is meegegeven bestond de sub al. In dat geval moet er geüpdatet worden. Anders moet het toegevoegd worden onder vermelding van een naam/titel.
    {
        wijzigSub($id, $titel, $tekst, $reacties_aan, $categorieid);
    }
    else
    {
        $id = nieuweSub($titel, $tekst, $reacties_aan, $categorieid);
    }

    nieuweMelding('Pagina bewerkt.');
    $returnUrl = 'toonsub.php?id=' . $id;
}
