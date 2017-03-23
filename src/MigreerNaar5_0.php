<?php
namespace Cyndaron;

require __DIR__ . '/../check.php';

class MigreerNaar5_0 extends Pagina
{
    public function __construct()
    {
        DBConnection::geefEen('ALTER TABLE subs MODIFY tekst MEDIUMTEXT', []);
        DBConnection::geefEen('ALTER TABLE vorigesubs MODIFY tekst MEDIUMTEXT', []);

        parent::__construct('Upgrade naar versie 5.0');
        $this->toonPrepagina();
        echo 'De upgrade is voltooid.';
        $this->toonPostPagina();
    }
}



