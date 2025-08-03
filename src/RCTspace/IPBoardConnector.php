<?php
declare(strict_types=1);

namespace Cyndaron\RCTspace;

use Cyndaron\DBAL\Connection;
use Cyndaron\Util\SettingsRepository;
use function dirname;

final class IPBoardConnector
{
    public readonly Connection $connection;
    public readonly string $offurlPath;
    public readonly string $offurlPathRideExchange;

    public function __construct(SettingsRepository $sr)
    {
        $dbName = $sr->get('rctspace_forum_database');
        $username = $sr->get('rctspace_forum_user');
        $password = $sr->get('rctspace_forum_password');
        $this->connection = Connection::create('mysql', 'localhost', $dbName, $username, $password);
        $offurlPath = $sr->get('rctspace_forum_offurlPath');
        $this->offurlPath = $offurlPath;
        $offurlPathRx = dirname($offurlPath, 2) . '/rx/zipv3/';
        $this->offurlPathRideExchange = $offurlPathRx;
    }
}
