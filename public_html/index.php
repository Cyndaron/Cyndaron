<?php
require_once __DIR__ . '/../vendor/autoload.php';

const PUB_DIR = __DIR__;
const ROOT_DIR = __DIR__ . '/../';
const CACHE_DIR = ROOT_DIR . 'var/cache/';

chdir(ROOT_DIR);

$kernel = new \Cyndaron\WebBootstrapper();
$kernel->boot();
