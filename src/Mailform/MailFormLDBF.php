<?php
namespace Cyndaron\Mailform;

use Cyndaron\Util\Mail\Mail;
use Cyndaron\Request\RequestParameters;
use Cyndaron\View\Template\Template;
use Symfony\Component\Mime\Address;

final class MailFormLDBF
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
        'geboortedag',
        'geboortemaand',
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
        $mail1 = new Mail(
            new Address('voorzitter@leendebroekertfonds.nl'),
            'Nieuwe aanvraag',
            null,
            $this->mailBody
        );
        $mail1->addReplyTo(new Address($requesterMail));

        $mail2 = new Mail(
            new Address($requesterMail),
            'Kopie aanvraag',
            null,
            $this->mailBody
        );
        $mail2->addReplyTo(new Address('voorzitter@leendebroekertfonds.nl'));

        return $mail1->send() && $mail2->send();
    }
}
