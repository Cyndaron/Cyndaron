<?php
declare(strict_types=1);

namespace Cyndaron\LDBF;

use Cyndaron\DBAL\DatabaseError;
use Cyndaron\Error\ErrorPage;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\Page;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Ticketsale\Util;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\Util\MailFactory;
use Cyndaron\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use function strlen;
use function str_contains;
use function str_starts_with;

final class Controller
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly PageRenderer $pageRenderer,
        private readonly MailFactory $mailFactory,
        private readonly MailformRenderer $mailformRenderer,
    ) {
    }

    #[RouteAttribute('process-mailform', RequestMethod::POST, UserLevel::ANONYMOUS, skipCSRFCheck: true)]
    public function processMailform(RequestParameters $post, RequestRepository $requestRepository, HttpRequest $httpRequest): Response
    {
        $requesterMail = $post->getEmail('E-mailadres');
        if ($post->isEmpty())
        {
            throw new IncompleteData('Ongeldig formulier.');
        }
        if (empty($requesterMail))
        {
            throw new IncompleteData('U heeft uw e-mailadres niet of niet goed ingevuld. Klik op Vorige om het te herstellen.');
        }
        try
        {
            $toAddress = new Address($requesterMail);
        }
        catch (RfcComplianceException)
        {
            throw new IncompleteData('U heeft een ongeldig e-mailadres ingevuld. Klik op Vorige om het te herstellen.');
        }

        $initials = $post->getUnfilteredString('voorletters');
        if (strlen($initials) > 10 || str_contains($initials, 'http:') || str_contains($initials, 'https:'))
        {
            throw new IncompleteData('Ongeldige tekens in voornaamveld! Klik op Vorige om het te herstellen.');
        }

        $dateOfBirth = $post->getUnfilteredString('geboortedatum');
        if (strlen($dateOfBirth) > 20)
        {
            throw new IncompleteData('Ongeldige tekens in veld geboortedatum! Klik op Vorige om het te herstellen.');
        }

        $telephone = $post->getUnfilteredString('telefoon');
        if (str_starts_with($telephone, '+1-'))
        {
            throw new IncompleteData('Ongeldige tekens in veld voor telefoonnummer! Klik op Vorige om het te herstellen.');
        }

        $mailBody = $this->mailformRenderer->renderMailBody($post, $this->templateRenderer);
        $request = new Request();
        $request->secretCode = Util::generateSecretCode();
        $request->email = $requesterMail;
        $request->mailBody = $mailBody;
        $request->confirmed = false;
        $requestRepository->save($request);

        $confirmationLink = "{$httpRequest->getSchemeAndHttpHost()}/ldbf/confirm/{$request->id}/{$request->secretCode}";

        $mail = $this->mailFactory->createMailWithDefaults(
            $toAddress,
            'Bevestig uw e-mailadres',
            "Om uw aanvraag te voltooien, klikt u op de volgende link: {$confirmationLink}",
            "Om uw aanvraag te voltooien, klikt u op de volgende link: <a href=\"{$confirmationLink}\">{$confirmationLink}</a>"
        );
        $sent = $mail->send();

        if (!$sent)
        {
            $page = new ErrorPage('Aanvraag mislukt', 'Wij konden geen e-mail sturen om uw adres te bevestigen.');
            return $this->pageRenderer->renderErrorResponse($page);
        }

        $page = Page::createSimple(
            'Bevestig uw e-mailadres',
            'Wij hebben u een e-mail gestuurd om uw adres te bevestigen. Klik op de link in de e-mail om uw aanvraag te voltooien.'
        );
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('confirm', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function confirm(QueryBits $queryBits, RequestRepository $requestRepository): Response
    {
        $requestId = $queryBits->getInt(2);
        $request = $requestRepository->fetchById($requestId);
        if ($request === null)
        {
            $page = new ErrorPage('Bevestigen mislukt', 'Kon de aanvraag niet vinden.', Response::HTTP_BAD_REQUEST);
            return $this->pageRenderer->renderErrorResponse($page);
        }

        $secretCode = $queryBits->getString(3);
        if ($request->secretCode !== $secretCode)
        {
            $page = new ErrorPage('Bevestigen mislukt', 'Kon de aanvraag niet vinden.', Response::HTTP_BAD_REQUEST);
            return $this->pageRenderer->renderErrorResponse($page);
        }

        $request->confirmed = true;
        $requestRepository->save($request);

        $mailSent = $this->sendRequestMail($request);
        if (!$mailSent)
        {
            throw new DatabaseError('Wegens een technisch probleem is het versturen van de e-mail niet gelukt.');
        }

        $page = Page::createSimple('Aanvraag verstuurd', 'Het versturen is gelukt. U krijgt nog een kopie van de aanvraag per e-mail.');
        return $this->pageRenderer->renderResponse($page);
    }

    private function sendRequestMail(Request $request): bool
    {
        $mail1 = $this->mailFactory->createMailWithDefaults(
            new Address('voorzitter@leendebroekertfonds.nl'),
            'Nieuwe aanvraag',
            null,
            $request->mailBody
        );
        $mail1->addReplyTo(new Address($request->email));

        $mail2 = $this->mailFactory->createMailWithDefaults(
            new Address($request->email),
            'Kopie aanvraag',
            null,
            $request->mailBody
        );
        $mail2->addReplyTo(new Address('voorzitter@leendebroekertfonds.nl'));

        return $mail1->send() && $mail2->send();
    }
}
