<?php
require_once('functies.db.php');
require_once('pagina.php');
$id = intval(geefGetVeilig('id'));
$connectie = newPDO();
$formprep = $connectie->prepare('SELECT * FROM mailformulieren WHERE id=?');
$formprep->execute(array($id));
$form = $formprep->fetch();

if ($form['naam'])
{
    if (strtolower(geefPostVeilig('antispam')) == strtolower($form['antispamantwoord']))
    {
        foreach (array_keys($_POST) as $vraag)
        {
            $vraag = wasVariabele($vraag);

            if ($vraag !== 'antispam')
                $tekst .= $vraag . ': ' . strtr(geefPostVeilig($vraag), array('\\' => '')) . "\n";
        }
        $ontvanger = $form['mailadres'];
        $onderwerp = $form['naam'];
        if (geefPostVeilig('E-mailadres'))
        {
            $extraheaders = 'From: ' . geefPostVeilig('E-mailadres');
        }
        else
        {
            $server = str_replace("www.", "", $_SERVER['HTTP_HOST']);
            $server = str_replace("http://", "", $server);
            $server = str_replace("https://", "", $server);
            $server = str_replace("/", "", $server);
            $extraheaders = 'From: noreply@' . $server;
        }
        if (mail($ontvanger, $onderwerp, $tekst, $extraheaders))
        {
            $pagina = new Pagina('Formulier verstuurd');
            $pagina->maakNietDelen(true);
            $pagina->toonPrePagina();
            echo 'Het versturen is gelukt.';
        }
        else
        {
            $pagina = new Pagina('Formulier versturen mislukt');
            $pagina->maakNietDelen(true);
            $pagina->toonPrePagina();
            echo 'Wegens een technisch probleem is het versturen niet gelukt';
        }
        $pagina->toonPostPagina();
    }
    else
    {
        $pagina = new Pagina('Formulier versturen mislukt');
        $pagina->maakNietDelen(true);
        $pagina->toonPrePagina();
        echo 'U heef de antispamvraag niet of niet goed ingevuld. Klik op vorige om het te herstellen.';
        $pagina->toonPostPagina();
    }
}
else
{
    $pagina = new Pagina('Formulier versturen mislukt');
    $pagina->maakNietDelen(true);
    $pagina->toonPrePagina();
    echo 'Ongeldig formulier.';
    $pagina->toonPostPagina();
}