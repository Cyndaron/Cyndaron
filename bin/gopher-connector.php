#!/usr/bin/env php
<?php
use Cyndaron\DBAL\DBConnection;
use Cyndaron\Gopher\MenuEntryFactory;
use Cyndaron\Util\Setting;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

const ROOT_DIR = __DIR__ . '/../';
// Referenced by some code
const PUB_DIR = __DIR__ . '/../public_html';
const CACHE_DIR = ROOT_DIR . 'var/cache/gopher/';

$gopherDomain = $argv[1];
$gopherSubdomain = $argv[2];
$gopherPort = $argv[3];
$query = $argv[4];

$pdo = \Cyndaron\DBAL\Connection::create('mysql', $dbplek ?? 'localhost', $dbnaam, $dbuser, $dbpass);
DBConnection::connect($pdo);
Setting::load($pdo);
$menuEntryFactory = new MenuEntryFactory($gopherDomain, $gopherSubdomain, $gopherPort);
$urlService = new \Cyndaron\Url\UrlService($pdo, '', []);
$controller = new \Cyndaron\Gopher\Controller($menuEntryFactory, $urlService);

$response = $controller->processQuery($query);
echo $response->encode();
