<?php
namespace Cyndaron\Mailform;

use Cyndaron\Request;
use Cyndaron\Template\Template;

class MailFormLDBF
{
    private const MAIL_TEMPLATE_VARS = [
        'geslacht',
        'voorletters',
        'achternaam',
        'adres',
        'postcode',
        'woonplaats',
        'telefoon',
        'emailadres',
        'bvoornamen',
        'bachternaam',
        'bwoonadres',
        'geboortejaar',
        'studie',
        'lessen',
        'lesdocent',
        'aantal',
        'vervolg',
        'soort',
        'huur',
        'anderszins',
        'gezinsinkomen',
        'ookaanvraag',
    ];

    private Template $mailTemplate;
    private string $mailBody;

    public function fillMailTemplate(): void
    {
        $this->mailTemplate = new Template();
        $templateVars = [];
        foreach (self::MAIL_TEMPLATE_VARS as $templateVar)
        {
            $requestVarName = $templateVar;
            if ($templateVar === 'emailadres')
                $requestVarName = 'E-mailadres';

            $templateVars[$templateVar] = Request::post($requestVarName);
        }
        $this->mailBody = $this->mailTemplate->render('Mailform/LDBFMail.blade.php', $templateVars);
    }

    public function sendMail(): bool
    {
        $extraheaders = 'From: "Website Leen de Broekert Fonds" <noreply@leendebroekertfonds.nl>' . "\n" .
            'Content-Type: text/html; charset="UTF-8"';
        $extraheadersMail1 = $extraheaders . "\n" . 'Reply-To: ' . Request::post('E-mailadres');
        $extraheadersMail2 = $extraheaders . "\n" . 'Reply-To: voorzitter@leendebroekertfonds.nl';

        $mail1 = mail('voorzitter@leendebroekertfonds.nl', 'Nieuwe aanvraag', $this->mailBody, $extraheadersMail1, '-fnoreply@leendebroekertfonds.nl');
        $mail2 = mail(Request::post('E-mailadres'), 'Kopie aanvraag', $this->mailBody, $extraheadersMail2, '-fnoreply@leendebroekertfonds.nl');

        return $mail1 && $mail2;
    }
}