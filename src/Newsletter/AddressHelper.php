<?php
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\DBAL\Connection;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Util\Setting;
use Cyndaron\Util\SettingsRepository;
use RuntimeException;
use Symfony\Component\Mime\Address;
use function base64_encode;
use function class_exists;
use function hash;

final class AddressHelper
{
    public function __construct(
        private readonly Connection $connection,
        private readonly UrlInfo $urlInfo,
        private readonly SettingsRepository $sr,
    ) {
    }

    public function getConfirmationLink(string $email): string
    {
        $base64email = base64_encode($email);
        $code = self::calculateHash($email);
        return "{$this->urlInfo->schemeAndHost}/newsletter/confirm/$base64email/$code";
    }

    public function getUnsubscribeLink(string $email): string
    {
        $base64email = base64_encode($email);
        $code = self::calculateHash($email);
        return "{$this->urlInfo->schemeAndHost}/newsletter/unsubscribe/$base64email/$code";
    }

    public function unsubscribe(string $email): EmailPresenceStatistics
    {
        $prep = $this->connection->prepare('UPDATE users SET optOut = 1 WHERE email = ?');
        $prep->execute([$email]);
        $numChangedUsers = $prep->rowCount();

        $numChangedMembers = 0;
        if (class_exists('\Cyndaron\Geelhoed\Member\Member'))
        {
            $prep = $this->connection->prepare('UPDATE users SET optOut = 1 WHERE id IN (SELECT userId FROM geelhoed_members WHERE parentEmail = ?)');
            $prep->execute([$email]);
            $numChangedMembers = $prep->rowCount();
        }

        $prep = $this->connection->prepare('DELETE FROM newsletter_subscriber WHERE email = ?');
        $prep->execute([$email]);
        $numChangedSubscribers = $prep->rowCount();

        return new EmailPresenceStatistics($numChangedUsers, $numChangedMembers, $numChangedSubscribers);
    }

    public function delete(string $email): EmailPresenceStatistics
    {
        $prep = $this->connection->prepare('UPDATE users SET email = NULL WHERE email = ?');
        $prep->execute([$email]);
        $numChangedUsers = $prep->rowCount();

        $numChangedMembers = 0;
        if (class_exists('\Cyndaron\Geelhoed\Member\Member'))
        {
            $prep = $this->connection->prepare('UPDATE geelhoed_members SET parentEmail = \'\' WHERE parentEmail = ?');
            $prep->execute([$email]);
            $numChangedMembers = $prep->rowCount();
        }

        $prep = $this->connection->prepare('DELETE FROM newsletter_subscriber WHERE email = ?');
        $prep->execute([$email]);
        $numChangedSubscribers = $prep->rowCount();

        return new EmailPresenceStatistics($numChangedUsers, $numChangedMembers, $numChangedSubscribers);
    }

    /**
     * @return Address[]
     */
    public function getMemberAddresses(): array
    {
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
        $records = $this->connection->doQueryAndFetchAll($sql) ?: [];

        $memberAddresses = [];
        foreach ($records as $record)
        {
            try
            {
                /** @var string $mail */
                $mail = $record['mail'];
                $address = new Address($mail);
                $memberAddresses[] = $address;
            }
            catch (\Exception)
            {
            }
        }

        return $memberAddresses;
    }

    /**
     * @return Address[]
     */
    public function getSubscriberAddresses(SubscriberRepository $subscriberRepository): array
    {
        $subscriberAddresses = [];
        foreach ($subscriberRepository->fetchAll(['confirmed = 1']) as $subscriber)
        {
            try
            {
                $address = new Address($subscriber->email);
                $subscriberAddresses[] = $address;
            }
            catch (\Exception)
            {
            }
        }

        return $subscriberAddresses;
    }

    public function getFromAddress(): Address
    {
        $address = $this->sr->get('newsletter_from_address');
        $name = $this->sr->get('newsletter_from_name');
        if (!empty($address))
        {
            return new Address($address, $name);
        }

        return new Address("nieuwsbrief@{$this->urlInfo->domain}", $name);
    }

    public function getReplyToAddress(): Address
    {
        $address = $this->sr->get('newsletter_reply_to_address');
        $name = $this->sr->get('newsletter_reply_to_name');
        if (!empty($address))
        {
            return new Address($address, $name);
        }

        return new Address("info@{$this->urlInfo->domain}", $name);
    }

    public function getUnsubscribeAddress(): string
    {
        return "nieuwsbrief@{$this->urlInfo->domain}";
    }

    public function calculateHash(string $email): string
    {
        $salt = $this->sr->get('newsletter_salt');
        if ($salt === '')
        {
            throw new RuntimeException('Salt is niet ingesteld!');
        }
        return hash('sha256', $email . $salt);
    }
}
