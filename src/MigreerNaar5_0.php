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
        $connectie->doQuery('ALTER TABLE `mc_leden` ADD `newRenderer` INT(1) NOT NULL DEFAULT \'0\' AFTER `renderAvatarHaar`;');
        $connectie->doQuery('ALTER TABLE `fotoboeken` ADD `categorieid` INT NULL AFTER `notities`;');
        $connectie->doQuery('ALTER TABLE `fotoboeken` ADD INDEX(`categorieid`);');
        $connectie->doQuery('ALTER TABLE `mailformulieren` ADD `stuur_bevestiging` TINYINT(1) NOT NULL DEFAULT \'0\' AFTER `antispamantwoord`, ADD `tekst_bevestiging` TEXT NULL DEFAULT NULL AFTER `stuur_bevestiging`; ');

        parent::__construct('Upgrade naar versie 5.0');
        $this->toonPrepagina();
        echo 'De upgrade is voltooid.';
        $this->toonPostPagina();
    }

}