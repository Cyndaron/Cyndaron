<?php
namespace Cyndaron\LDBF;

use Cyndaron\Request\RequestParameters;
use Cyndaron\Util\MailFactory;
use Cyndaron\View\Template\TemplateRenderer;
use Symfony\Component\Mime\Address;

final class MailformRenderer
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

    public function __construct()
    {
    }

    public function renderMailBody(RequestParameters $post, TemplateRenderer $templateRenderer): string
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
        return $templateRenderer->render('LDBF/Mailform.blade.php', $templateVars);
    }
}
