<?php
require __DIR__ . '/../src/WebBootstrapper.php';

const PUB_DIR = __DIR__;
const ROOT_DIR = __DIR__ . '/../';

chdir(ROOT_DIR);

$kernel = new \Cyndaron\WebBootstrapper();
$kernel->boot();
