<?php
namespace Cyndaron\Mailform;

use Cyndaron\Request\RequestParameters;
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

    private string $mailBody;

    public function fillMailTemplate(RequestParameters $post): void
    {
        $mailTemplate = new Template();
        $templateVars = [];
        foreach (self::MAIL_TEMPLATE_VARS as $templateVar)
        {
            $requestVarName = $templateVar;
            if ($templateVar === 'emailadres')
            {
                $requestVarName = 'E-mailadres';
            }

            $templateVars[$templateVar] = $post->getHTML($requestVarName);
        }
        $this->mailBody = $mailTemplate->render('Mailform/LDBFMail.blade.php', $templateVars);
    }

    public function sendMail(string $requesterMail): bool
    {
        $extraheaders = 'From: "Website Leen de Broekert Fonds" <noreply@leendebroekertfonds.nl>' . "\n" .
            'Content-Type: text/html; charset="UTF-8"';
        $extraheadersMail1 = $extraheaders . "\n" . 'Reply-To: ' . $requesterMail;
        $extraheadersMail2 = $extraheaders . "\n" . 'Reply-To: voorzitter@leendebroekertfonds.nl';

        $mail1 = mail('voorzitter@leendebroekertfonds.nl', 'Nieuwe aanvraag', $this->mailBody, $extraheadersMail1, '-fnoreply@leendebroekertfonds.nl');
        $mail2 = mail($requesterMail, 'Kopie aanvraag', $this->mailBody, $extraheadersMail2, '-fnoreply@leendebroekertfonds.nl');

        return $mail1 && $mail2;
    }
}
