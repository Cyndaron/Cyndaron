<?php
require('check.php');
require_once('functies.db.php');

geefEen('ALTER TABLE subs MODIFY tekst MEDIUMTEXT', []);
geefEen('ALTER TABLE vorigesubs MODIFY tekst MEDIUMTEXT', []);

echo 'Upgrade voltooid.';
