<?php
declare(strict_types=1);

namespace Cyndaron\Newsletter\Report;

use Cyndaron\Util\SettingsRepository;
use IMAP\Connection as IMAPConnection;
use RuntimeException;
use function array_key_exists;
use function explode;
use function imap_check;
use function imap_delete;
use function imap_fetch_overview;
use function imap_fetchbody;
use function imap_fetchstructure;
use function imap_open;
use function property_exists;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;
use function is_object;
use function is_string;

class ReportCreator
{
    private const STATUS_CODE_TO_STATUS = [
        '4.2.2' => Status::MAILBOX_FULL,
        // Used by Yahoo
        '5.0.0' => Status::ADDRESS_DOES_NOT_EXIST,
        '5.1.1' => Status::ADDRESS_DOES_NOT_EXIST,
        '5.4.4' => Status::DOMAIN_DOES_NOT_EXIST,
        '5.5.0' => Status::ADDRESS_DOES_NOT_EXIST,
        '5.7.0' => Status::ADDRESS_FORMAT_INVALID,
    ];

    private IMAPConnection|null $connection;

    public function __construct(private readonly SettingsRepository $settingsRepository)
    {
        $this->connection = $this->createConnection();
    }

    private function createConnection(): IMAPConnection|null
    {
        $server = $this->settingsRepository->get('newsletter_imap_server');
        $username = $this->settingsRepository->get('newsletter_imap_username');
        $password = $this->settingsRepository->get('newsletter_imap_password');
        $imapReference = "{{$server}:993/imap/ssl}INBOX";

        $mbox = imap_open($imapReference, $username, $password);
        return $mbox instanceof IMAPConnection ? $mbox : null;
    }

    public function deleteMessageByUid(int $uid): bool
    {
        if (!$this->connection instanceof IMAPConnection)
        {
            throw new RuntimeException('No connection to IMAP server!');
        }
        return imap_delete($this->connection, (string)$uid, FT_UID);
    }

    /**
     * @return Result[]
     */
    public function create(): array
    {
        if (!$this->connection instanceof IMAPConnection)
        {
            return [];
        }

        /** @var Result[] $reports */
        $reports = [];
        $mailboxInfo = imap_check($this->connection);
        if (!is_object($mailboxInfo) || !property_exists($mailboxInfo, 'Nmsgs'))
        {
            return [];
        }

        /** @var object{ message_id: string, subject: string, from: string, to: string, date: string, size: int, msgno: int, uid: int }[]|false $messages */
        $messages = imap_fetch_overview($this->connection, "1:{$mailboxInfo->Nmsgs}", 0);
        if ($messages === false)
        {
            return [];
        }

        foreach ($messages as $message)
        {
            $messageNum = $message->msgno;
            $structure = imap_fetchstructure($this->connection, $messageNum);

            if (!is_object($structure) || !property_exists($structure, 'parts'))
            {
                continue;
            }

            foreach ($structure->parts as $partNumber => $part)
            {
                if ($part->subtype !== 'RFC822')
                {
                    continue;
                }

                $body = imap_fetchbody($this->connection, $messageNum, (string)$partNumber);
                if (!is_string($body))
                {
                    continue;
                }

                $lines = explode("\r\n", $body);
                $statusCode = '';
                $email = '';
                foreach ($lines as $line)
                {
                    if (str_starts_with($line, 'Status: '))
                    {
                        $statusCode = substr($line, strlen('Status: '));
                    }
                    elseif (str_starts_with($line, 'Original-Recipient: '))
                    {
                        $value = substr($line, strlen('Original-Recipient: '));
                        $email = trim(str_replace('rfc822;', '', $value));
                    }
                }

                if ($email !== '' && array_key_exists($statusCode, self::STATUS_CODE_TO_STATUS))
                {
                    $status = self::STATUS_CODE_TO_STATUS[$statusCode];
                    $proposedAction = self::getProposedAction($status);
                    $reports[] = new Result($message->uid, $email, $status, $proposedAction);
                }
            }
        }

        return $reports;
    }

    private static function getProposedAction(Status $input): Action
    {
        return match($input)
        {
            Status::ADDRESS_DOES_NOT_EXIST => Action::DELETE_ADDRESS,
            Status::ADDRESS_FORMAT_INVALID => Action::DELETE_ADDRESS,
            Status::DOMAIN_DOES_NOT_EXIST => Action::DELETE_ADDRESS,
            Status::MAILBOX_FULL => Action::WAIT,
        };
    }
}
