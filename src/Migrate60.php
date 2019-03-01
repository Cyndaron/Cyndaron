<?php
namespace Cyndaron;

require __DIR__ . '/../check.php';

class Migrate60 extends Pagina
{
    public function __construct()
    {
        DBConnection::doQuery("DELETE FROM instellingen WHERE naam = 'menutype' OR naam = 'facebook_share'");
        DBConnection::doQuery('DROP TABLE ideeen');
        DBConnection::doQuery('ALTER TABLE `mc_leden` ADD `newRenderer` BOOLEAN NOT NULL DEFAULT FALSE AFTER `renderAvatarHaar`;');
        DBConnection::doQuery('ALTER TABLE `mc_leden` ADD `uuid` CHAR(32) NULL DEFAULT NULL AFTER `mcnaam`;');
        DBConnection::doQuery('ALTER TABLE `mc_leden` ADD `skinUrl` CHAR(103) NULL DEFAULT NULL AFTER `donateur`;');

        DBConnection::doQuery("UPDATE `menu` SET link = CONCAT('/', link) WHERE link NOT LIKE 'http%'", []);
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = CONCAT('/', doel)", []);

        DBConnection::doQuery("UPDATE `menu` SET link = REPLACE(link, '/toonsub.php?id=', '/sub/')", []);
        DBConnection::doQuery("UPDATE `menu` SET link = REPLACE(link, '/tooncategorie.php?id=', '/category/')", []);
        DBConnection::doQuery("UPDATE `menu` SET link = REPLACE(link, '/toonfotoboek.php?id=', '/photoalbum/')", []);
        DBConnection::doQuery("UPDATE `menu` SET link = REPLACE(link, '/mc-leden', '/minecraft/members')", []);
        DBConnection::doQuery("UPDATE `menu` SET link = REPLACE(link, '/mc-status', '/minecraft/status')", []);
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/toonsub.php?id=', '/sub/')", []);
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/tooncategorie.php?id=', '/category/')", []);
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/toonfotoboek.php?id=', '/photoalbum/')", []);
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/mc-leden', '/minecraft/members')", []);
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/mc-status', '/minecraft/status')", []);

        DBConnection::doQuery("ALTER TABLE `kaartverkoop_concerten` CHANGE `gereserveerde_plaatsen_uitverkocht` `gereserveerde_plaatsen_uitverkocht` TINYINT(1) NOT NULL DEFAULT '0';")

        parent::__construct('Upgrade naar versie 6.0');
        $this->showPrePage();
        echo 'De upgrade is voltooid.';
        $this->showPostPage();
    }
}
