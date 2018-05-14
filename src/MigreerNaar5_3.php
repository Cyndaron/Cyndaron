<?php
namespace Cyndaron;

require __DIR__ . '/../check.php';

class MigreerNaar5_0 extends Pagina
{
    public function __construct()
    {
        $connectie = DBConnection::getInstance();
        $connectie->doQuery('ALTER TABLE `gebruikers` ADD `email` VARCHAR(255) NULL DEFAULT NULL AFTER `wachtwoord`;');

        parent::__construct('Upgrade naar versie 5.0');
        $this->toonPrepagina();
        echo 'De upgrade is voltooid.';
        $this->toonPostPagina();
    }

}