<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Util\Mail\Mail;
use Cyndaron\View\Page;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Throwable;

use function array_chunk;
use function array_diff;
use function array_filter;
use function array_map;
use function array_slice;
use function array_walk;
use function class_exists;
use function count;
use function \Safe\error_log;
use function trim;
use function filter_var;

class Controller extends \Cyndaron\Routing\Controller
{
    protected array $getRoutes = [
        'viewSubscribers' => ['level' => UserLevel::ADMIN, 'function' => 'viewSubscribers'],
        'compose' => ['level' => UserLevel::ADMIN, 'function' => 'compose'],
    ];

    protected array $postRoutes = [
        'subscribe' => ['level' => UserLevel::ANONYMOUS, 'function' => 'subscribe'],
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
            $page = new Page('Inschrijving nieuwsbrief', 'Er is iets misgegaan bij het invullen van het formulier.');
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

        $page = new Page('Inschrijving nieuwsbrief', $message);
        return new Response($page->render());
    }

    private function sendNewsletterMail(string $subject, string $body, array $addresses): bool
    {
        $addresses = array_filter($addresses, static function(string $address)
        {
            return
                (trim($address) !== '') &&
                filter_var($address, FILTER_VALIDATE_EMAIL);
        });
        if (count($addresses) === 0)
        {
            return true;
        }
        // Use multiple e-mails as necessary, to avoid rejection by spam filters.
        $addressChunks = array_chunk($addresses, 490);

        $fromAddress = new Address('nieuwsbrief@sportschoolgeelhoed.nl', 'Nieuwsbrief Sportschool Geelhoed');
        $infoAddress = new Address('info@sportschoolgeelhoed.nl');

        $transport = new SendmailTransport();
        $mailer = new Mailer($transport);

        try
        {
            foreach ($addressChunks as $addressChunk)
            {
                $email = (new Email())
                    ->from($fromAddress)
                    ->to($infoAddress)
                    ->subject($subject)
                    ->addReplyTo($infoAddress)
                    ->addBcc(...$addressChunk)
                    ->html($body);
                $mailer->send($email);
            }
        }
        catch (Throwable $e)
        {
            error_log((string)$e);
            return false;
        }

        return true;
    }

    private function getResponse(bool $sendingWasSuccessful): JsonResponse
    {
        if (!$sendingWasSuccessful)
        {
            return new JsonResponse(['error' => 'Kon de e-mail niet verzenden!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse();
    }

    protected function send(RequestParameters $post): JsonResponse
    {
        $subject = $post->getSimpleString('subject');
        $body = $post->getHTML('body');
        $recipient = $post->getAlphaNum('recipient');
        $recipientAddress = $post->getEmail('recipientAddress');
        if ($recipient === 'single')
        {
            $result = $this->sendNewsletterMail($subject, $body, [$recipientAddress]);
            return $this->getResponse($result);
        }

        $subscriberAddresses = array_map(
            static function(Subscriber $subscriber) { return $subscriber->email; },
            Subscriber::fetchAll()
        );

        $unsubscribe = '<hr><i>U ontvangt deze e-mail omdat u zich heeft ingeschreven voor de nieuwsbrief. Mail naar <a href="mailto:nieuwsbrief@sportschoolgeelhoed.nl">nieuwsbrief@sportschoolgeelhoed.nl</a> om u uit te schrijven.</i>';
        $result = $this->sendNewsletterMail($subject, $body . $unsubscribe, $subscriberAddresses);
        if ($result === false || $recipient === 'subscribers')
        {
            return $this->getResponse($result);
        }

        $parentMail = '';
        if (class_exists('\Cyndaron\Geelhoed\Member\Member'))
        {
            $parentMail = 'UNION
                SELECT parentEmail AS mail FROM geelhoed_members AS twee WHERE userId NOT IN (SELECT id FROM users WHERE optout = 1)';
        }

        $sql = "
            SELECT DISTINCT mail FROM (
                SELECT email AS mail FROM users AS een WHERE optout <> 1
                {$parentMail}
            ) AS drie WHERE mail IS NOT NULL;";
        $memberAddresses = array_map(
            static function(array $record) { return $record['mail']; },
            DBConnection::doQueryAndFetchAll($sql) ?: []
        );
        // Do not send an e-mail to people who already got one because they're a newsletter subscriber.
        $memberAddresses = array_diff($memberAddresses, $subscriberAddresses);
        $unsubscribe = '<hr><i>U ontvangt deze e-mail omdat u of uw kind(eren) lid zijn van Sportschool Geelhoed. Mail naar <a href="mailto:nieuwsbrief@sportschoolgeelhoed.nl">nieuwsbrief@sportschoolgeelhoed.nl</a> om u uit te schrijven.</i>';
        $result = $this->sendNewsletterMail($subject, $body . $unsubscribe, $memberAddresses);
        return $this->getResponse($result);
    }
}
