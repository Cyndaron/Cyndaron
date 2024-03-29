<?php
namespace Cyndaron\Mailform;

use Cyndaron\Mail\Mail;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Util\Mail as UtilMail;
use Cyndaron\View\Template\TemplateRenderer;
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

    public function fillMailTemplate(RequestParameters $post, TemplateRenderer $templateRenderer): void
    {
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
        $this->mailBody = $templateRenderer->render('Mailform/LDBFMail.blade.php', $templateVars);
    }

    public function sendMail(string $requesterMail): bool
    {
        $mail1 = UtilMail::createMailWithDefaults(
            new Address('voorzitter@leendebroekertfonds.nl'),
            'Nieuwe aanvraag',
            null,
            $this->mailBody
        );
        $mail1->addReplyTo(new Address($requesterMail));

        $mail2 = UtilMail::createMailWithDefaults(
            new Address($requesterMail),
            'Kopie aanvraag',
            null,
            $this->mailBody
        );
        $mail2->addReplyTo(new Address('voorzitter@leendebroekertfonds.nl'));

        return $mail1->send() && $mail2->send();
    }
}
