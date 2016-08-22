<?php
require('check.php');
require_once('functies.db.php');

geefEen('ALTER TABLE subs MODIFY tekst MEDIUMTEXT', array());
geefEen('ALTER TABLE vorigesubs MODIFY tekst MEDIUMTEXT', array());

echo 'Upgrade voltooid.';
