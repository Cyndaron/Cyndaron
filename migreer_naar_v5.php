<?php
require __DIR__ . '/check.php';
require_once __DIR__ . '/src/DBConnection.php';

use Cyndaron\DBConnection;

DBConnection::geefEen('ALTER TABLE subs MODIFY tekst MEDIUMTEXT', []);
DBConnection::geefEen('ALTER TABLE vorigesubs MODIFY tekst MEDIUMTEXT', []);

echo 'Upgrade voltooid.';
