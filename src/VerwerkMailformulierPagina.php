<?php
namespace Cyndaron;


class VerwerkMailformulierPagina extends Pagina
{
    public function __construct()
    {
        $id = intval(Request::geefGetVeilig('id'));
        $this->connectie = DBConnection::getPDO();
        $formprep = $this->connectie->prepare('SELECT * FROM mailformulieren WHERE id=?');
        $formprep->execute([$id]);
        $form = $formprep->fetch();
        $tekst = '';

        if ($form['naam'])
        {
            if (strtolower(Request::geefPostVeilig('antispam')) == strtolower($form['antispamantwoord']))
            {
                foreach (array_keys($_POST) as $vraag)
                {
                    $vraag = Request::wasVariabele($vraag);

                    if ($vraag !== 'antispam')
                    {
                        $tekst .= $vraag . ': ' . strtr(Request::geefPostVeilig($vraag), ['\\' => '']) . "\n";
                    }
                }
                $ontvanger = $form['mailadres'];
                $onderwerp = $form['naam'];
                if (Request::geefPostVeilig('E-mailadres'))
                {
                    $extraheaders = 'From: ' . Request::geefPostVeilig('E-mailadres');
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