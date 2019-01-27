<?php
namespace Cyndaron;

require __DIR__ . '/../check.php';

class Migrate60 extends Pagina
{
    public function __construct()
    {
        $connection = DBConnection::getInstance();
        $connection->doQuery('DELETE FROM instellingen WHERE naam = "menutype"');
        $connection->doQuery('DROP TABLE ideeen');
        $connection->doQuery('ALTER TABLE `mc_leden` ADD `newRenderer` BOOLEAN NOT NULL DEFAULT FALSE AFTER `renderAvatarHaar`;');

        parent::__construct('Upgrade naar versie 6.0');
        $this->toonPrepagina();
        echo 'De upgrade is voltooid.';
        $this->toonPostPagina();
    }
}
