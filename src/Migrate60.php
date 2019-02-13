<?php
namespace Cyndaron;

require __DIR__ . '/../check.php';

class Migrate60 extends Pagina
{
    public function __construct()
    {
        $connection = DBConnection::getInstance();
        $connection->doQuery("DELETE FROM instellingen WHERE naam = 'menutype' OR naam = 'facebook_share'");
        $connection->doQuery('DROP TABLE ideeen');
        $connection->doQuery('ALTER TABLE `mc_leden` ADD `newRenderer` BOOLEAN NOT NULL DEFAULT FALSE AFTER `renderAvatarHaar`;');
        $connection->doQuery('ALTER TABLE `mc_leden` ADD `uuid` CHAR(32) NULL DEFAULT NULL AFTER `mcnaam`;');
        $connection->doQuery('ALTER TABLE `mc_leden` ADD `skinUrl` CHAR(103) NULL DEFAULT NULL AFTER `donateur`;');

        $connection->doQuery("UPDATE `menu` SET link = CONCAT('/', link) WHERE link NOT LIKE 'http%'", []);
        $connection->doQuery("UPDATE `friendlyurls` SET doel = CONCAT('/', doel)", []);


        $connection->doQuery("UPDATE `menu` SET link = REPLACE(link, 'tooncategorie.php?id=', '/category/') WHERE link LIKE '/tooncategorie.php?id=%'", []);
        $connection->doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, 'tooncategorie.php?id=', '/category/') WHERE doel LIKE '/tooncategorie.php?id=%';", []);

        parent::__construct('Upgrade naar versie 6.0');
        $this->toonPrepagina();
        echo 'De upgrade is voltooid.';
        $this->toonPostPagina();
    }
}
