<?php
namespace Cyndaron;

require __DIR__ . '/../check.php';

class MigreerNaar5_3 extends Pagina
{
    public function __construct()
    {
        $connectie = DBConnection::getInstance();
        $connectie->doQuery('ALTER TABLE `gebruikers` ADD `email` VARCHAR(255) NULL DEFAULT NULL AFTER `wachtwoord`;');

        $connectie->doQuery('ALTER TABLE `categorieen` ADD `categorieid` INT NULL AFTER `beschrijving`;', []);
        $connectie->doQuery('ALTER TABLE `categorieen` ADD FOREIGN KEY (`categorieid`) REFERENCES `categorieen`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;', []);

        parent::__construct('Upgrade naar versie 5.3');
        $this->toonPrepagina();
        echo 'De upgrade is voltooid.';
        $this->toonPostPagina();
    }

}