<?php
namespace Cyndaron;

use Cyndaron\User\User;
use Cyndaron\User\UserLevel;

class MigrateController extends Controller
{
    protected $minLevelGet = UserLevel::ANONYMOUS;

    const VERSIONS = [
        '5.3' => 'migrate53',
        '6.0' => 'migrate60',
    ];

    public function routeGet()
    {
        $version = $this->action;

        if (array_key_exists($version, static::VERSIONS))
        {
            $method = static::VERSIONS[$version];
            $this->$method();
            $page = new Page('Upgrade naar versie ' . $version, 'De upgrade is voltooid.');
            $page->showPrePage();
            $page->showBody();
            $page->showPostPage();
        }
    }

    private function migrate53()
    {
        if (!User::isAdmin())
            die();

        DBConnection::doQuery('ALTER TABLE `gebruikers` ADD `email` VARCHAR(255) NULL DEFAULT NULL AFTER `wachtwoord`;');
        DBConnection::doQuery('ALTER TABLE `gebruikers` ADD UNIQUE( `email`);');

        DBConnection::doQuery('ALTER TABLE `categorieen` ADD `categorieid` INT NULL AFTER `beschrijving`;', []);
        DBConnection::doQuery('ALTER TABLE `categorieen` ADD FOREIGN KEY (`categorieid`) REFERENCES `categorieen`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;', []);

        DBConnection::doQuery('ALTER TABLE `menu` ADD `isDropdown` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `alias`, ADD `isImage` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `isDropdown`;', []);
        // Bestaande menu-items porten
        DBConnection::doQuery('UPDATE `menu` SET link = REPLACE(link, \'#dd\', \'\'), isDropdown=1 WHERE link LIKE \'%#dd\'', []);
        DBConnection::doQuery('UPDATE `menu` SET alias = REPLACE(alias, \'img#\', \'\'), isImage=1 WHERE alias LIKE \'%img#\'', []);
    }

    private function migrate60()
    {
        DBConnection::doQuery("RENAME TABLE gebruikers TO users;");
        DBConnection::doQuery("ALTER TABLE `users` CHANGE `gebruikersnaam` `username` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `users` CHANGE `wachtwoord` `password` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `users` CHANGE `niveau` `level` INT(1) NOT NULL;");

        if (!User::isAdmin())
            die();

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
        DBConnection::doQuery("UPDATE `menu` SET link = REPLACE(link, '/wieiswie', '/user/gallery')", []);
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/toonsub.php?id=', '/sub/')", []);
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/tooncategorie.php?id=', '/category/')", []);
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/toonfotoboek.php?id=', '/photoalbum/')", []);
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/mc-leden', '/minecraft/members')", []);
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/mc-status', '/minecraft/status')", []);
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/wieiswie', '/user/gallery')", []);

        DBConnection::doQuery("ALTER TABLE `kaartverkoop_concerten` CHANGE `gereserveerde_plaatsen_uitverkocht` `gereserveerde_plaatsen_uitverkocht` TINYINT(1) NOT NULL DEFAULT '0';");

        DBConnection::doQuery("ALTER TABLE `users` ADD `firstname` VARCHAR(100) NOT NULL DEFAULT '' AFTER `level`, ADD `tussenvoegsel` VARCHAR(50) NOT NULL DEFAULT '' AFTER `firstname`, ADD `lastname` VARCHAR(200) NOT NULL DEFAULT '' AFTER `tussenvoegsel`, ADD `role` VARCHAR(100) NOT NULL DEFAULT '' AFTER `lastname`, ADD `comments` VARCHAR(500) NOT NULL DEFAULT '' AFTER `role`, ADD `avatar` VARCHAR(250) NOT NULL DEFAULT '' AFTER `comments`;");
        DBConnection::doQuery("ALTER TABLE `users` ADD `hide_from_member_list` TINYINT(1) NOT NULL DEFAULT '0' AFTER `avatar`;");

        DBConnection::doQuery("ALTER TABLE `mailformulieren` ADD `send_confirmation` TINYINT(1) NOT NULL DEFAULT '0' AFTER `antispamantwoord`, ADD `confirmation_text` TEXT NULL DEFAULT NULL AFTER `send_confirmation`; ");

        DBConnection::doQuery("ALTER TABLE `subs` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `categorieid`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`; ");
        DBConnection::doQuery("ALTER TABLE `categorieen` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `categorieid`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`; ");
        DBConnection::doQuery("ALTER TABLE `fotoboeken` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `categorieid`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`; ");
        DBConnection::doQuery("ALTER TABLE `mailformulieren` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `confirmation_text`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`; ");
    }
}
;