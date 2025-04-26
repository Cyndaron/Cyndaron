#!/usr/bin/env php
<?php
use Cyndaron\Gopher\MenuEntryFactory;
use Cyndaron\Util\Setting;

require __DIR__ . '/../vendor/autoload.php';
/** @var \Cyndaron\Base\CyndaronConfig $config */
$config = require __DIR__ . '/../config.php';

const ROOT_DIR = __DIR__ . '/../';
// Referenced by some code
const PUB_DIR = __DIR__ . '/../public_html';
const CACHE_DIR = ROOT_DIR . 'var/cache/gopher/';

$gopherDomain = $argv[1];
$gopherSubdomain = $argv[2];
$gopherPort = $argv[3];
$query = $argv[4];

$dic = new \Cyndaron\Util\DependencyInjectionContainer();
$connection = \Cyndaron\DBAL\Connection::create(
    'mysql',
        $config->databaseHost ?? 'localhost',
    $config->databaseName,
    $config->databaseUser,
    $config->databasePassword,
);
$dic->add($connection);
Setting::load($connection);
$menuEntryFactory = new MenuEntryFactory($gopherDomain, $gopherSubdomain, $gopherPort);
$dic->add($menuEntryFactory);
$request = new Symfony\Component\HttpFoundation\Request([]);
$dic->add($request);

$controller = $dic->createClassWithDependencyInjection(\Cyndaron\Gopher\Controller::class);

$response = $controller->processQuery($query);
echo $response->encode();
