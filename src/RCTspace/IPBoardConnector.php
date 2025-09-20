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
    public readonly string $offurlPathRideExchangeZip;
    public readonly string $offurlPathRideExchangeZipOld;
    public readonly string $offurlPathRideExchangeZipOlder;
    public readonly string $offurlPathRideExchangePlain;

    public function __construct(SettingsRepository $sr)
    {
        $dbName = $sr->get('rctspace_forum_database');
        $username = $sr->get('rctspace_forum_user');
        $password = $sr->get('rctspace_forum_password');
        $this->connection = Connection::create('mysql', 'localhost', $dbName, $username, $password);
        $offurlPath = $sr->get('rctspace_forum_offurlPath');
        $this->offurlPath = $offurlPath;
        $offurlPathRxZip = dirname($offurlPath, 2) . '/rx/zipv3/';
        $offurlPathRxZipOld = dirname($offurlPath, 2) . '/rx/zipv2/';
        $offurlPathRxZipOlder = dirname($offurlPath, 2) . '/rx/zip/';
        $offurlPathRxPlain = dirname($offurlPath, 2) . '/rx/tracks/';
        $this->offurlPathRideExchangeZip = $offurlPathRxZip;
        $this->offurlPathRideExchangeZipOld = $offurlPathRxZipOld;
        $this->offurlPathRideExchangeZipOlder = $offurlPathRxZipOlder;
        $this->offurlPathRideExchangePlain = $offurlPathRxPlain;
    }
}
