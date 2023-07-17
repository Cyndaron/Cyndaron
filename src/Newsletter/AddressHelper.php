<?php
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use Symfony\Component\Mime\Address;
use function base64_encode;
use function class_exists;
use function hash;

final class AddressHelper
{
    public static function getUnsubscribeLink(string $email): string
    {
        $base64email = base64_encode($email);
        $code = self::calculateHash($email);
        $domain = Util::getDomain();
        return "https://{$domain}/newsletter/unsubscribe/$base64email/$code";
    }

    public static function unsubscribe(string $email): EmailPresenceStatistics
    {
        $pdo = DBConnection::getPDO();
        $prep = $pdo->prepare('UPDATE users SET optOut = 1 WHERE email = ?');
        $prep->execute([$email]);
        $numChangedUsers = $prep->rowCount();

        $numChangedMembers = 0;
        if (class_exists('\Cyndaron\Geelhoed\Member\Member'))
        {
            $prep = $pdo->prepare('UPDATE users SET optOut = 1 WHERE id IN (SELECT userId FROM geelhoed_members WHERE parentEmail = ?)');
            $prep->execute([$email]);
            $numChangedMembers = $prep->rowCount();
        }

        $prep = $pdo->prepare('DELETE FROM newsletter_subscriber WHERE email = ?');
        $prep->execute([$email]);
        $numChangedSubscribers = $prep->rowCount();

        return new EmailPresenceStatistics($numChangedUsers, $numChangedMembers, $numChangedSubscribers);
    }

    public static function delete(string $email): EmailPresenceStatistics
    {
        $pdo = DBConnection::getPDO();
        $prep = $pdo->prepare('UPDATE users SET email = NULL WHERE email = ?');
        $prep->execute([$email]);
        $numChangedUsers = $prep->rowCount();

        $numChangedMembers = 0;
        if (class_exists('\Cyndaron\Geelhoed\Member\Member'))
        {
            $prep = $pdo->prepare('UPDATE geelhoed_members SET parentEmail = NULL WHERE parentEmail = ?');
            $prep->execute([$email]);
            $numChangedMembers = $prep->rowCount();
        }

        $prep = $pdo->prepare('DELETE FROM newsletter_subscriber WHERE email = ?');
        $prep->execute([$email]);
        $numChangedSubscribers = $prep->rowCount();

        return new EmailPresenceStatistics($numChangedUsers, $numChangedMembers, $numChangedSubscribers);
    }

    /**
     * @return Address[]
     */
    public static function getMemberAddresses(): array
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
        $records = DBConnection::doQueryAndFetchAll($sql) ?: [];

        $memberAddresses = [];
        foreach ($records as $record)
        {
            try
            {
                $address = new Address($record['mail']);
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
    public static function getSubscriberAddresses(): array
    {
        $subscriberAddresses = [];
        foreach (Subscriber::fetchAll() as $subscriber)
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

    public static function getFromAddress(): Address
    {
        $address = Setting::get('newsletter_from_address');
        $name = Setting::get('newsletter_from_name');
        if (!empty($address))
        {
            return new Address($address, $name);
        }

        $domain = Util::getDomain();
        return new Address("nieuwsbrief@{$domain}", $name);
    }

    public static function getReplyToAddress(): Address
    {
        $address = Setting::get('newsletter_reply_to_address');
        $name = Setting::get('newsletter_reply_to_name');
        if (!empty($address))
        {
            return new Address($address, $name);
        }

        $domain = Util::getDomain();
        return new Address("info@{$domain}", $name);
    }

    public static function getUnsubscribeAddress(): string
    {
        $domain = Util::getDomain();
        return "nieuwsbrief@{$domain}";
    }

    public static function calculateHash(string $email): string
    {
        $salt = Setting::get('newsletter_salt');
        return hash('sha256', $email . $salt);
    }
}
