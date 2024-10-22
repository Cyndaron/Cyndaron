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

assert($client instanceof Socket);
assert(is_string($query));

$pdo = \Cyndaron\DBAL\Connection::connect('mysql', $dbplek ?? 'localhost', $dbnaam, $dbuser, $dbpass);
DBConnection::connect($pdo);
Setting::load($pdo);
$menuEntryFactory = new MenuEntryFactory(GOPHER_DOMAIN, GOPHER_SUBDOMAIN, PORT);
$urlService = new \Cyndaron\Url\UrlService($pdo, '', []);
$controller = new \Cyndaron\Gopher\Controller($menuEntryFactory, $urlService);

echo "Query: {$query}\n";

$response = $controller->processQuery($query);
$response->send($client);
