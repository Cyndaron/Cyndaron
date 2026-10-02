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
use function imap_expunge;
use function assert;

class ReportCreator
{
    private const STATUS_CODE_TO_STATUS = [
        '4.2.2' => Status::MAILBOX_FULL,
        // Used by Yahoo
        '5.0.0' => Status::ADDRESS_DOES_NOT_EXIST,
        '5.1.0' => Status::DOMAIN_DOES_NOT_EXIST,
        '5.1.1' => Status::ADDRESS_DOES_NOT_EXIST,
        // Used by Microsoft
        '5.2.2' => Status::MAILBOX_FULL,
        '5.4.4' => Status::DOMAIN_DOES_NOT_EXIST,
        '5.5.0' => Status::ADDRESS_DOES_NOT_EXIST,
        '5.7.0' => Status::ADDRESS_FORMAT_INVALID,
        '5.7.1' => Status::ADDRESS_DOES_NOT_EXIST,
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

    public function expungeDeletedMessages(): bool
    {
        if (!$this->connection instanceof IMAPConnection)
        {
            throw new RuntimeException('No connection to IMAP server!');
        }
        return imap_expunge($this->connection);
    }

    /**
     * @return string[]
     */
    private function getLinesFromBody(int $messageNum, int $partNumber): array
    {
        assert($this->connection !== null);
        $body = imap_fetchbody($this->connection, $messageNum, (string)$partNumber);
        if (!is_string($body))
        {
            return [];
        }

        return explode("\r\n", $body);
    }

    private function checkForErrorReport(int $messageNum, int $messageUid, int $partNumber): Result|null
    {
        $lines = $this->getLinesFromBody($messageNum, $partNumber);
        if (empty($lines))
        {
            return null;
        }

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

        if ($email === '' || !array_key_exists($statusCode, self::STATUS_CODE_TO_STATUS))
        {
            return null;
        }

        $status = self::STATUS_CODE_TO_STATUS[$statusCode];
        $proposedAction = self::getProposedAction($status);
        return new Result($messageUid, $email, $status, $proposedAction);
    }

    private function checkForOutOfOffice(int $messageNum, int $messageUid, int $partNumber, string $email): Result|null
    {
        $lines = $this->getLinesFromBody($messageNum, $partNumber);
        if (empty($lines))
        {
            return null;
        }

        foreach ($lines as $line)
        {
            if ($line === 'X-Auto-Response-Suppress: All')
            {
                return new Result($messageUid, $email, Status::OUT_OF_OFFICE, Action::DELETE_REPORT);
            }
        }

        return null;
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

        /** @var object{ message_id: string, subject: string, from: string, to: string, date: string, size: int, msgno: int, uid: int, deleted: bool }[]|false $messages */
        $messages = imap_fetch_overview($this->connection, "1:{$mailboxInfo->Nmsgs}", 0);
        if ($messages === false)
        {
            return [];
        }

        foreach ($messages as $message)
        {
            if ($message->deleted)
            {
                continue;
            }

            $messageNum = $message->msgno;
            $structure = imap_fetchstructure($this->connection, $messageNum);

            if (!is_object($structure) || !property_exists($structure, 'parts'))
            {
                continue;
            }

            foreach ($structure->parts as $partNumber => $part)
            {
                $report = null;
                if ($part->subtype === 'RFC822')
                {
                    $report = $this->checkForErrorReport($messageNum, $message->uid, $partNumber);

                }
                elseif ($part->subtype === 'PLAIN')
                {
                    $report = $this->checkForOutOfOffice($messageNum, $message->uid, $partNumber, $message->from);
                }

                if ($report instanceof Result)
                {
                    $reports[] = $report;
                }

                //if ($message->uid == 2875) { echo '<pre>'; var_dump($part); var_dump($lines); echo '</pre>'; };

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
            Status::OUT_OF_OFFICE => Action::DELETE_REPORT,
        };
    }
}
