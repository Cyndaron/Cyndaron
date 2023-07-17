<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Request\QueryBits;
use Cyndaron\User\User;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserLevel;
use Cyndaron\View\SimplePage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;

use function array_map;
use function array_sum;
use function base64_decode;
use function class_exists;
use function array_udiff;

class Controller extends \Cyndaron\Routing\Controller
{
    protected array $getRoutes = [
        'viewSubscribers' => ['level' => UserLevel::ADMIN, 'function' => 'viewSubscribers'],
        'compose' => ['level' => UserLevel::ADMIN, 'function' => 'compose'],
        'unsubscribe' => ['level' => UserLevel::ANONYMOUS, 'function' => 'unsubscribeUser'],
    ];

    protected array $postRoutes = [
        'subscribe' => ['level' => UserLevel::ANONYMOUS, 'function' => 'subscribe'],
        'unsubscribe' => ['level' => UserLevel::ADMIN, 'function' => 'unsubscribeAdmin'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'unsubscribe'],
    ];

    protected array $apiPostRoutes = [
        'send' => ['level' => UserLevel::ADMIN, 'function' => 'send'],
    ];

    protected function viewSubscribers(): Response
    {
        $page = new ViewSubscribersPage();
        return new Response($page->render());
    }

    protected function compose(): Response
    {
        $page = new SendNewsletterPage();
        return new Response($page->render());
    }

    protected function subscribe(RequestParameters $post): Response
    {
        $antiSpam = $post->getUnfilteredString('antispam');
        if ($antiSpam !== '')
        {
            $page = new SimplePage('Inschrijving nieuwsbrief', 'Er is iets misgegaan bij het invullen van het formulier.');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }

        $name = $post->getSimpleString('name');
        $email = $post->getEmail('email');

        $existing = Subscriber::fetch(['email = ?'], [$email]);
        if ($existing !== null)
        {
            $message = 'U was al ingeschreven voor de nieuwsbrief.';
        }
        else
        {
            $subscription = new Subscriber();
            $subscription->name = $name;
            $subscription->email = $email;
            $subscription->save();

            $message = 'U bent ingeschreven voor de nieuwsbrief.';
        }

        $page = new SimplePage('Inschrijving nieuwsbrief', $message);
        return new Response($page->render());
    }

    private function getResponse(int $numFailed, int $total): JsonResponse
    {
        if ($numFailed > 0)
        {
            return new JsonResponse(['error' => "Kon niet alle e-mails verzenden: {$numFailed} van {$total} mails zijn niet verzonden!"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse();
    }

    protected function send(RequestParameters $post, Request $request): JsonResponse
    {
        $subject = $post->getSimpleString('subject');
        $body = $post->getHTML('body');
        /** @var UploadedFile[] $attachments */
        $attachments = ($request->files->get('attachments')) ?: [];
        $newsletterContents = new NewsletterContents($subject, $body, $attachments);

        $recipientGroup = RecipientGroup::from($post->getAlphaNum('recipient'));
        $replyToAddress = AddressHelper::getReplyToAddress();
        $fromAddress = AddressHelper::getFromAddress();
        $unsubscribeAddress = new Address(AddressHelper::getUnsubscribeAddress());

        $sender = new Sender($fromAddress, $replyToAddress, $unsubscribeAddress, $newsletterContents);

        if ($recipientGroup === RecipientGroup::SINGLE)
        {
            $recipientAddress = new Address($post->getEmail('recipientAddress'));
            $numFailed = $sender->send($recipientAddress) ? 0 : 1;
            return $this->getResponse($numFailed, 1);
        }

        $numFailed = 0;
        $total = 0;
        $subscriberAddresses = AddressHelper::getSubscriberAddresses();
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
            $memberAddresses = AddressHelper::getMemberAddresses();
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

    protected function unsubscribeUser(QueryBits $queryBits): Response
    {
        $email = base64_decode($queryBits->getString(2), true);
        if ($email === false)
        {
            return new Response((new SimplePage('Uitschrijven', 'Ongeldig e-mailadres!.'))->render(), Response::HTTP_BAD_REQUEST);
        }

        $code = $queryBits->getString(3);
        if ($code !== AddressHelper::calculateHash($email))
        {
            return new Response((new SimplePage('Uitschrijven', 'Controlecode klopt niet! Mogelijk heeft u een oude link gebruik of klopt de configuratie niet.'))->render(), Response::HTTP_BAD_REQUEST);
        }

        $changes = AddressHelper::unsubscribe($email);
        if ($changes->total() === 0)
        {
            return new Response((new SimplePage('Uitschrijven', 'Wij konden uw adres niet vinden. Mogelijk bent u al uitgeschreven.'))->render(), Response::HTTP_BAD_REQUEST);
        }

        return new Response((new SimplePage('Uitgeschreven', 'U bent uitgeschreven voor de nieuwsbrief.'))->render());
    }

    protected function unsubscribeAdmin(RequestParameters $post): Response
    {
        $email = $post->getEmail('email');
        $changes = AddressHelper::unsubscribe($email);

        if ($changes->total() === 0)
        {
            User::addNotification('Adres niet gevonden!');
        }
        else
        {
            $notification = "Adres uitgeschreven.
                {$changes->users} gebruikersrecord(s), {$changes->members} ledenrecord(s) en {$changes->subscribers} nieuwsbriefinschrijver(s) aangepast.";
            User::addNotification($notification);
        }

        return new RedirectResponse('/newsletter/viewSubscribers#unsubscribe');
    }

    protected function delete(RequestParameters $post): Response
    {
        $email = $post->getEmail('email');
        $changes = AddressHelper::delete($email);

        if ($changes->total() === 0)
        {
            User::addNotification('Adres niet gevonden!');
        }
        else
        {
            $notification = "Adres verwijderd.
                {$changes->users} gebruikersrecord(s), {$changes->members} ledenrecord(s) en {$changes->subscribers} nieuwsbriefinschrijver(s) aangepast.";
            User::addNotification($notification);
        }

        return new RedirectResponse('/newsletter/viewSubscribers#delete');
    }
}
