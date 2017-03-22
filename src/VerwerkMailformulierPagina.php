<?php
namespace Cyndaron;

require_once __DIR__ . '/../functies.db.php';

class VerwerkMailformulierPagina extends Pagina
{
    public function __construct()
    {
        $id = intval(geefGetVeilig('id'));
        $this->connectie = newPDO();
        $formprep = $this->connectie->prepare('SELECT * FROM mailformulieren WHERE id=?');
        $formprep->execute(array($id));
        $form = $formprep->fetch();
        $tekst = '';

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
                    parent::__construct('Formulier verstuurd');
                    $this->maakNietDelen(true);
                    $this->toonPrePagina();
                    echo 'Het versturen is gelukt.';
                }
                else
                {
                    parent::__construct('Formulier versturen mislukt');
                    $this->maakNietDelen(true);
                    $this->toonPrePagina();
                    echo 'Wegens een technisch probleem is het versturen niet gelukt';
                }
                $this->toonPostPagina();
            }
            else
            {
                parent::__construct('Formulier versturen mislukt');
                $this->maakNietDelen(true);
                $this->toonPrePagina();
                echo 'U heef de antispamvraag niet of niet goed ingevuld. Klik op vorige om het te herstellen.';
                $this->toonPostPagina();
            }
        }
        else
        {
            parent::__construct('Formulier versturen mislukt');
            $this->maakNietDelen(true);
            $this->toonPrePagina();
            echo 'Ongeldig formulier.';
            $this->toonPostPagina();
        }
    }
}