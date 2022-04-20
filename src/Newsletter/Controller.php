<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\User\User;
use Cyndaron\Util\Util;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserLevel;
use Cyndaron\View\SimplePage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
use function array_sum;
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
        'unsubscribe' => ['level' => UserLevel::ADMIN, 'function' => 'unsubscribe'],
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

    /**
     * @param string $subject
     * @param string $body
     * @param string[] $addresses
     * @param UploadedFile[] $attachments
     * @throws \Safe\Exceptions\ErrorfuncException
     * @return bool
     */
    private function sendNewsletterMail(string $subject, string $body, array $addresses, array $attachments = []): bool
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

        $fromAddress = new Address($this->getFromAddress(), 'Nieuwsbrief Sportschool Geelhoed');
        $infoAddress = new Address('info@sportschoolgeelhoed.nl');

        $transport = new SendmailTransport();
        $mailer = new Mailer($transport);

        $unsubscribeAddress = $this->getUnsubscribeAddress();

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
                foreach ($attachments as $attachment)
                {
                    $email->attachFromPath($attachment->getPath(), $attachment->getClientOriginalName(), $attachment->getClientMimeType());
                }
                $email->getHeaders()->addTextHeader('List-Unsubscribe', "<mailto:{$unsubscribeAddress}>");
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

    protected function send(RequestParameters $post, Request $request): JsonResponse
    {
        /** @var UploadedFile[] $attachments */
        $attachments = ($request->files->get('attachments')) ?: [];

        $subject = $post->getSimpleString('subject');
        $body = $post->getHTML('body');
        $recipient = $post->getAlphaNum('recipient');
        $recipientAddress = $post->getEmail('recipientAddress');
        $unsubscribeAddress = $this->getUnsubscribeAddress();
        if ($recipient === 'single')
        {
            $result = $this->sendNewsletterMail($subject, $body, [$recipientAddress], $attachments);
            return $this->getResponse($result);
        }

        $subscriberAddresses = array_map(
            static function(Subscriber $subscriber)
            {
                return $subscriber->email;
            },
            Subscriber::fetchAll()
        );

        $unsubscribe = '<hr><i>U ontvangt deze e-mail omdat u zich heeft ingeschreven voor de nieuwsbrief. Mail naar <a href="mailto:' . $unsubscribeAddress . '">' . $unsubscribeAddress . '</a> om u uit te schrijven.</i>';
        $result = $this->sendNewsletterMail($subject, $body . $unsubscribe, $subscriberAddresses, $attachments);
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
            static function(array $record)
            {
                return $record['mail'];
            },
            DBConnection::doQueryAndFetchAll($sql) ?: []
        );
        // Do not send an e-mail to people who already got one because they're a newsletter subscriber.
        $memberAddresses = array_diff($memberAddresses, $subscriberAddresses);
        $unsubscribe = '<hr><i>U ontvangt deze e-mail omdat u of uw kind(eren) lid zijn van Sportschool Geelhoed. Mail naar <a href="mailto:' . $unsubscribeAddress . '">' . $unsubscribeAddress . '</a> om u uit te schrijven.</i>';
        $result = $this->sendNewsletterMail($subject, $body . $unsubscribe, $memberAddresses, $attachments);
        return $this->getResponse($result);
    }

    private function getFromAddress(): string
    {
        $domain = Util::getDomain();
        return "nieuwsbrief@{$domain}";
    }

    private function getUnsubscribeAddress(): string
    {
        $domain = Util::getDomain();
        return "nieuwsbrief@{$domain}";
    }

    protected function unsubscribe(RequestParameters $post): Response
    {
        $changes = [
            'users' => 0,
            'members' => 0,
            'subscribers' => 0,
        ];

        $email = $post->getEmail('email');
        $pdo = DBConnection::getPDO();
        $prep = $pdo->prepare('UPDATE users SET optOut = 1 WHERE email = ?');
        $prep->execute([$email]);
        $changes['users'] = $prep->rowCount();

        if (class_exists('\Cyndaron\Geelhoed\Member\Member'))
        {
            $prep = $pdo->prepare('UPDATE users SET optOut = 1 WHERE id IN (SELECT userId FROM geelhoed_members WHERE parentEmail = ?)');
            $prep->execute([$email]);
            $changes['members'] = $prep->rowCount();
        }

        $prep = $pdo->prepare('DELETE FROM newsletter_subscriber WHERE email = ?');
        $prep->execute([$email]);
        $changes['subscribers'] = $prep->rowCount();

        if (array_sum($changes) === 0)
        {
            User::addNotification('Adres niet gevonden!');
        }
        else
        {
            $notifcation = "Adres uitgeschreven.
                {$changes['users']} gebruikersrecord(s), {$changes['members']} ledenrecord(s) en {$changes['subscribers']} nieuwsbriefinschrijver(s) aangepast.";
            User::addNotification($notifcation);
        }

        return new RedirectResponse('/newsletter/viewSubscribers#unsubscribe');
    }
}
