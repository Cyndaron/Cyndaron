<?php
namespace Cyndaron\Mailform;

use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Template\Template;

class VerwerkMailFormulierPaginaLDBF extends Page
{
    public function __construct()
    {
        if (!Request::postIsEmpty())
        {
            if (empty(Request::post('E-mailadres')))
            {
                parent::__construct('Formulier versturen mislukt');
                $this->body = 'U heeft uw e-mailadres niet of niet goed ingevuld. Klik op Vorige om het te herstellen.';
                $this->render();
            }
            else
            {
                $template = new Template();
                $mailBody = $template->render('Mailform/LDBFMail.blade.php', [
                    'geslacht' => Request::post('geslacht'),
                    'voorletters' => Request::post('voorletters'),
                    'achternaam' => Request::post('achternaam'),
                    'adres' => Request::post('adres'),
                    'postcode' => Request::post('postcode'),
                    'woonplaats' => Request::post('woonplaats'),
                    'telefoon' => Request::post('telefoon'),
                    'emailadres' => Request::post('E-mailadres'),
                    'bvoornamen' => Request::post('bvoornamen'),
                    'bachternaam' => Request::post('bachternaam'),
                    'bwoonadres' => Request::post('bwoonadres'),
                    'geboortejaar' => Request::post('geboortejaar'),
                    'studie' => Request::post('studie'),
                    'lessen' => Request::post('lessen'),
                    'lesdocent' => Request::post('lesdocent'),
                    'aantal' => Request::post('aantal'),
                    'vervolg' => Request::post('vervolg'),
                    'soort' => Request::post('soort'),
                    'huur' => Request::post('huur'),
                    'anderszins' => Request::post('anderszins'),
                    'gezinsinkomen' => Request::post('gezinsinkomen'),
                    'ookaanvraag' => Request::post('ookaanvraag'),
                ]);

                $extraheaders = 'From: "Website Leen de Broekert Fonds" <noreply@leendebroekertfonds.nl>' . "\n" .
                    'Content-Type: text/html; charset="UTF-8"';
                $extraheadersMail1 = $extraheaders . "\n" . 'Reply-To: ' . Request::post('E-mailadres');
                $extraheadersMail2 = $extraheaders . "\n" . 'Reply-To: voorzitter@leendebroekertfonds.nl';

                $mail1 = mail('voorzitter@leendebroekertfonds.nl', 'Nieuwe aanvraag', $mailBody, $extraheadersMail1, '-fnoreply@leendebroekertfonds.nl');
                $mail2 = mail(Request::post('E-mailadres'), 'Kopie aanvraag', $mailBody, $extraheadersMail2, '-fnoreply@leendebroekertfonds.nl');

                if ($mail1 && $mail2)
                {
                    parent::__construct('Formulier verstuurd');
                    $this->body = 'Het versturen is gelukt.';
                }
                else
                {
                    parent::__construct('Formulier versturen mislukt');
                    $this->body = 'Wegens een technisch probleem is het versturen niet gelukt';
                }

                $this->render();
            }
        }
        else
        {
            parent::__construct('Formulier versturen mislukt');
            $this->body = 'Ongeldig formulier.';
            $this->render();
        }
    }
}