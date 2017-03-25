<?php
namespace Cyndaron;

require __DIR__ . '/../check.php';

class MigreerNaar5_0 extends Pagina
{
    public function __construct()
    {
        $connectie = DBConnection::getInstance();
        $connectie->doQuery('ALTER TABLE `ideeen` ADD `datum` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `tekst`;');
        $connectie->doQuery('ALTER TABLE `mc_leden` ADD `renderAvatarHaar` INT(1) NOT NULL DEFAULT \'1\' AFTER `donateur`;');
        $connectie->doQuery('ALTER TABLE `fotoboeken` ADD `categorieid` INT NULL AFTER `notities`;');
        $connectie->doQuery('ALTER TABLE `fotoboeken` ADD INDEX(`categorieid`);');

        parent::__construct('Upgrade naar versie 5.0');
        $this->toonPrepagina();
        echo 'De upgrade is voltooid.';
        $this->toonPostPagina();
    }

}