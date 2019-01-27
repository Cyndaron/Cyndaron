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
            if ($form['stuur_bevestiging'] == true && empty(Request::geefPostVeilig('E-mailadres')))
            {
                parent::__construct('Formulier versturen mislukt');
                $this->maakNietDelen(true);
                $this->toonPrePagina();
                echo 'U heeft uw e-mailadres niet of niet goed ingevuld. Klik op Vorige om het te herstellen.';
                $this->toonPostPagina();
            }
            elseif (strtolower(Request::geefPostVeilig('antispam')) == strtolower($form['antispamantwoord']))
            {
                foreach (Request::geefPostArrayVeilig() as $vraag => $antwoord)
                {
                    if ($vraag !== 'antispam')
                    {
                        $tekst .= $vraag . ': ' . strtr($antwoord, ['\\' => '']) . "\n";
                    }
                }
                $ontvanger = $form['mailadres'];
                $onderwerp = $form['naam'];
                $afzender = Request::geefPostVeilig('E-mailadres');

                $server = str_replace("www.", "", $_SERVER['HTTP_HOST']);
                $server = str_replace("http://", "", $server);
                $server = str_replace("https://", "", $server);
                $server = str_replace("/", "", $server);
                $extraheaders = 'From: noreply@' . $server;

                if ($afzender)
                {
                    $extraheaders .= "\r\n" . 'Reply-To: ' . $afzender;
                }

                if (mail($ontvanger, $onderwerp, $tekst, $extraheaders))
                {
                    parent::__construct('Formulier verstuurd');
                    $this->maakNietDelen(true);
                    $this->toonPrePagina();
                    echo 'Het versturen is gelukt.';

                    if ($form['stuur_bevestiging'] == true && $afzender)
                    {
                        $extraheaders = sprintf('From: %s <noreply@%s>', html_entity_decode(Setting::get('websitenaam')), $server);
                        $extraheaders .= "\r\n" . 'Reply-To: ' . $ontvanger;
                        mail($afzender, 'Ontvangstbevestiging', $form['tekst_bevestiging'], $extraheaders);
                    }
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
                echo 'U heeft de antispamvraag niet of niet goed ingevuld. Klik op Vorige om het te herstellen.';
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