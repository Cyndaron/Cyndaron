<?php
require __DIR__ . '/../src/WebBootstrapper.php';

chdir(__DIR__ . '/../');

$kernel = new \Cyndaron\WebBootstrapper();
$kernel->boot();
