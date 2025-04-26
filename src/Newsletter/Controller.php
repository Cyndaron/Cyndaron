<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserSession;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\SettingsRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use function array_udiff;
use function base64_decode;

class Controller
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly SubscriberRepository $subscriberRepository,
        private readonly AddressHelper $addressHelper,
    ) {
    }

    #[RouteAttribute('viewSubscribers', RequestMethod::GET, UserLevel::ADMIN)]
    public function viewSubscribers(CSRFTokenHandler $tokenHandler): Response
    {
        $page = new ViewSubscribersPage($tokenHandler, $this->subscriberRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('compose', RequestMethod::GET, UserLevel::ADMIN)]
    public function compose(CSRFTokenHandler $tokenHandler): Response
    {
        $page = new SendNewsletterPage($tokenHandler);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('subscribe', RequestMethod::POST, UserLevel::ANONYMOUS)]
    public function subscribe(RequestParameters $post, SettingsRepository $sr): Response
    {
        $antiSpam = $post->getUnfilteredString('antispam');
        $antiSpam2 = $post->getUnfilteredString('new_password');
        if ($antiSpam !== 'geelhoed' || $antiSpam2 !== '')
        {
            $page = new SimplePage('Inschrijving nieuwsbrief', 'Er is iets misgegaan bij het invullen van het formulier.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }

        $name = $post->getSimpleString('name');
        $email = $post->getEmail('email');

        $existing = $this->subscriberRepository->fetch(['email = ?'], [$email]);
        if ($existing !== null)
        {
            $message = 'U was al ingeschreven voor de nieuwsbrief.';
        }
        else
        {
            $subscription = new Subscriber();
            $subscription->name = $name;
            $subscription->email = $email;
            $subscription->confirmed = false;
            $this->subscriberRepository->save($subscription);

            $this->sendConfirmationMail(new Address($email, $name), $sr);

            $message = 'Wij hebben een e-mail gestuurd naar uw e-mailadres. Klik op de link in de e-mail om de inschrijving te bevestigen.';
        }

        $page = new SimplePage('Inschrijving nieuwsbrief', $message);
        return $this->pageRenderer->renderResponse($page);
    }

    private function sendConfirmationMail(Address $toAddress, SettingsRepository $sr): void
    {
        $replyToAddress = $this->addressHelper->getReplyToAddress();
        $fromAddress = $this->addressHelper->getFromAddress();
        $organisation = $sr->get(BuiltinSetting::ORGANISATION);
        $confirmationLink = $this->addressHelper->getConfirmationLink($toAddress->getAddress());

        $transport = new SendmailTransport();
        $mailer = new Mailer($transport);
        $email = (new Email())
            ->from($fromAddress)
            ->to($toAddress)
            ->subject('Inschrijving bevestigen')
            ->addReplyTo($replyToAddress)
            ->html("Om uw nieuwsbriefinschrijving voor {$organisation} te bevestigen, <a href=\"{$confirmationLink}\">klikt u hier</a>.");
        $mailer->send($email);
    }

    private function getResponse(int $numFailed, int $total): JsonResponse
    {
        if ($numFailed > 0)
        {
            return new JsonResponse(['error' => "Kon niet alle e-mails verzenden: {$numFailed} van {$total} mails zijn niet verzonden!"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse();
    }

    #[RouteAttribute('send', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function send(RequestParameters $post, Request $request): JsonResponse
    {
        $subject = $post->getSimpleString('subject');
        $body = $post->getHTML('body');
        /** @var UploadedFile[] $attachments */
        $attachments = ($request->files->get('attachments')) ?: [];
        $newsletterContents = new NewsletterContents($subject, $body, $attachments);

        $recipientGroup = RecipientGroup::from($post->getAlphaNum('recipient'));
        $replyToAddress = $this->addressHelper->getReplyToAddress();
        $fromAddress = $this->addressHelper->getFromAddress();
        $unsubscribeAddress = new Address($this->addressHelper->getUnsubscribeAddress());

        $sender = new Sender($this->addressHelper, $fromAddress, $replyToAddress, $unsubscribeAddress, $newsletterContents);

        if ($recipientGroup === RecipientGroup::SINGLE)
        {
            $recipientAddress = new Address($post->getEmail('recipientAddress'));
            $numFailed = $sender->send($recipientAddress) ? 0 : 1;
            return $this->getResponse($numFailed, 1);
        }

        $numFailed = 0;
        $total = 0;
        $subscriberAddresses = $this->addressHelper->getSubscriberAddresses($this->subscriberRepository);
        foreach ($subscriberAddresses as $subscriberAddress)
        {
            $total++;
            if ($sender->send($subscriberAddress) === false)
            {
                $numFailed++;
            }
        }

        if ($recipientGroup === RecipientGroup::EVERYONE)
        {
            $memberAddresses = $this->addressHelper->getMemberAddresses();
            // Do not send an e-mail to people who already got one because they're a newsletter subscriber.
            $memberAddresses = array_udiff($memberAddresses, $subscriberAddresses, static function(Address $address1, Address $address2)
            {
                return $address1->getAddress() <=> $address2->getAddress();
            });

            foreach ($memberAddresses as $memberAddress)
            {
                $total++;
                if ($sender->send($memberAddress) === false)
                {
                    $numFailed++;
                }
            }
        }

        return $this->getResponse($numFailed, $total);
    }

    #[RouteAttribute('unsubscribe', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function unsubscribeUser(QueryBits $queryBits): Response
    {
        $email = base64_decode($queryBits->getString(2), true);
        if ($email === false)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Uitschrijven', 'Ongeldig e-mailadres!.'), status:  Response::HTTP_BAD_REQUEST);
        }

        $code = $queryBits->getString(3);
        if ($code !== $this->addressHelper->calculateHash($email))
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Uitschrijven', 'Controlecode klopt niet! Mogelijk heeft u een oude link gebruikt of klopt de configuratie niet.'), status:  Response::HTTP_BAD_REQUEST);
        }

        $changes = $this->addressHelper->unsubscribe($email);
        if ($changes->total() === 0)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Uitschrijven', 'Wij konden uw adres niet vinden. Mogelijk bent u al uitgeschreven.'), status:  Response::HTTP_BAD_REQUEST);
        }

        return $this->pageRenderer->renderResponse(new SimplePage('Uitgeschreven', 'U bent uitgeschreven voor de nieuwsbrief.'));
    }

    #[RouteAttribute('confirm', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function confirm(QueryBits $queryBits): Response
    {
        $email = base64_decode($queryBits->getString(2), true);
        if ($email === false)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Inschrijven', 'Ongeldig e-mailadres!.'), status:  Response::HTTP_BAD_REQUEST);
        }

        $code = $queryBits->getString(3);
        if ($code !== $this->addressHelper->calculateHash($email))
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Inschrijven', 'Controlecode klopt niet! Mogelijk heeft u een oude link gebruikt of klopt de configuratie niet.'), status:  Response::HTTP_BAD_REQUEST);
        }

        $subscription = $this->subscriberRepository->fetch(['email = ?'], [$email]);
        if ($subscription === null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Inschrijven', 'Wij konden uw adres niet vinden. Probeer opnieuw in te schrijven.'), status:  Response::HTTP_BAD_REQUEST);
        }

        $subscription->confirmed = true;
        $this->subscriberRepository->save($subscription);

        return $this->pageRenderer->renderResponse(new SimplePage('Inschrijven', 'U bent ingeschreven voor de nieuwsbrief.'));
    }

    #[RouteAttribute('unsubscribe', RequestMethod::POST, UserLevel::ADMIN)]
    public function unsubscribeAdmin(RequestParameters $post, UserSession $userSession): Response
    {
        $email = $post->getEmail('email');
        $changes = $this->addressHelper->unsubscribe($email);

        if ($changes->total() === 0)
        {
            $userSession->addNotification('Adres niet gevonden!');
        }
        else
        {
            $notification = "Adres uitgeschreven.
                {$changes->users} gebruikersrecord(s), {$changes->members} ledenrecord(s) en {$changes->subscribers} nieuwsbriefinschrijver(s) aangepast.";
            $userSession->addNotification($notification);
        }

        return new RedirectResponse('/newsletter/viewSubscribers#unsubscribe');
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN)]
    public function delete(RequestParameters $post, UserSession $userSession): Response
    {
        $email = $post->getEmail('email');
        $changes = $this->addressHelper->delete($email);

        if ($changes->total() === 0)
        {
            $userSession->addNotification('Adres niet gevonden!');
        }
        else
        {
            $notification = "Adres verwijderd.
                {$changes->users} gebruikersrecord(s), {$changes->members} ledenrecord(s) en {$changes->subscribers} nieuwsbriefinschrijver(s) aangepast.";
            $userSession->addNotification($notification);
        }

        return new RedirectResponse('/newsletter/viewSubscribers#delete');
    }
}
