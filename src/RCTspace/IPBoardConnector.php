<?php
declare(strict_types=1);

namespace Cyndaron\RCTspace;

use Cyndaron\DBAL\Connection;
use Cyndaron\Util\SettingsRepository;

final class IPBoardConnector
{
    public readonly Connection $connection;
    public readonly string $offurlPath;

    public function __construct(SettingsRepository $sr)
    {
        $dbName = $sr->get('rctspace_forum_database');
        $username = $sr->get('rctspace_forum_user');
        $password = $sr->get('rctspace_forum_password');
        $this->connection = Connection::create('mysql', 'localhost', $dbName, $username, $password);
        $this->offurlPath = $sr->get('rctspace_forum_offurlPath');
    }
}
