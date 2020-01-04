<?php
require __DIR__ . '/src/Kernel.php';

chdir(__DIR__);

$kernel = new \Cyndaron\Kernel();
$kernel->boot();
