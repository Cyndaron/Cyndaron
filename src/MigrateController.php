<?php
/** @noinspection PhpUnusedPrivateMethodInspection */
/** @noinspection SqlResolve */

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

    protected function routeGet()
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
        {
            die();
        }

        DBConnection::doQuery('ALTER TABLE `gebruikers` ADD `email` VARCHAR(255) NULL DEFAULT NULL AFTER `wachtwoord`;');
        DBConnection::doQuery('ALTER TABLE `gebruikers` ADD UNIQUE( `email`);');

        DBConnection::doQuery('ALTER TABLE `categorieen` ADD `categorieid` INT NULL AFTER `beschrijving`;');
        DBConnection::doQuery('ALTER TABLE `categorieen` ADD FOREIGN KEY (`categorieid`) REFERENCES `categorieen`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;');

        DBConnection::doQuery('ALTER TABLE `menu` ADD `isDropdown` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `alias`, ADD `isImage` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `isDropdown`;');
        // Bestaande menu-items porten
        DBConnection::doQuery('UPDATE `menu` SET link = REPLACE(link, \'#dd\', \'\'), isDropdown=1 WHERE link LIKE \'%#dd\'');
        DBConnection::doQuery('UPDATE `menu` SET alias = REPLACE(alias, \'img#\', \'\'), isImage=1 WHERE alias LIKE \'%img#\'');
    }

    private function migrate60()
    {
        DBConnection::doQuery("RENAME TABLE gebruikers TO users;");
        DBConnection::doQuery("ALTER TABLE `users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `users` CHANGE `gebruikersnaam` `username` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `users` CHANGE `wachtwoord` `password` VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `users` CHANGE `niveau` `level` INT(1) NOT NULL;");

        if (!User::isAdmin())
        {
            die();
        }

        DBConnection::doQuery('DROP TABLE `vorigeartikelen`');
        DBConnection::doQuery('DROP TABLE `ideeen`');

        DBConnection::doQuery("UPDATE `menu` SET link = CONCAT('/', link) WHERE link NOT LIKE 'http%'");
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = CONCAT('/', doel)");

        DBConnection::doQuery("UPDATE `menu` SET link = REPLACE(link, '/toonsub.php?id=', '/sub/')");
        DBConnection::doQuery("UPDATE `menu` SET link = REPLACE(link, '/tooncategorie.php?id=', '/category/')");
        DBConnection::doQuery("UPDATE `menu` SET link = REPLACE(link, '/toonfotoboek.php?id=', '/photoalbum/')");
        DBConnection::doQuery("UPDATE `menu` SET link = REPLACE(link, '/mc-leden', '/minecraft/members')");
        DBConnection::doQuery("UPDATE `menu` SET link = REPLACE(link, '/mc-status', '/minecraft/status')");
        DBConnection::doQuery("UPDATE `menu` SET link = REPLACE(link, '/wieiswie', '/user/gallery')");
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/toonsub.php?id=', '/sub/')");
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/tooncategorie.php?id=', '/category/')");
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/toonfotoboek.php?id=', '/photoalbum/')");
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/mc-leden', '/minecraft/members')");
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/mc-status', '/minecraft/status')");
        DBConnection::doQuery("UPDATE `friendlyurls` SET doel = REPLACE(doel, '/wieiswie', '/user/gallery')");

        DBConnection::doQuery("ALTER TABLE `users` ADD `firstName` VARCHAR(100) NOT NULL DEFAULT '' AFTER `level`, ADD `tussenvoegsel` VARCHAR(50) NOT NULL DEFAULT '' AFTER `firstName`, ADD `lastName` VARCHAR(200) NOT NULL DEFAULT '' AFTER `tussenvoegsel`, ADD `role` VARCHAR(100) NOT NULL DEFAULT '' AFTER `lastName`, ADD `comments` VARCHAR(500) NOT NULL DEFAULT '' AFTER `role`, ADD `avatar` VARCHAR(250) NOT NULL DEFAULT '' AFTER `comments`;");
        DBConnection::doQuery("ALTER TABLE `users` ADD `hideFromMemberList` TINYINT(1) NOT NULL DEFAULT '0' AFTER `avatar`;");
        DBConnection::doQuery("ALTER TABLE `users` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `hideFromMemberList`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;");

        DBConnection::doQuery("ALTER TABLE `subs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `subs` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `categorieid`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;");
        DBConnection::doQuery("ALTER TABLE `subs` CHANGE `naam` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `subs` CHANGE `tekst` `text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `subs` CHANGE `reacties_aan` `enableComments` INT(1) NOT NULL DEFAULT '0';");
        DBConnection::doQuery("ALTER TABLE `subs` CHANGE `categorieid` `categoryId` INT(11) NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `subs` ADD `showBreadcrumbs` TINYINT(1) NOT NULL DEFAULT '0' AFTER `categoryId`;");
        DBConnection::doQuery("ALTER TABLE `subs` ADD `tags` VARCHAR(750) NOT NULL DEFAULT ''  AFTER `showBreadcrumbs`;");
        DBConnection::doQuery("ALTER TABLE `subs` ADD INDEX( `tags`); ");

        DBConnection::doQuery("RENAME TABLE vorigesubs TO sub_backups;");
        DBConnection::doQuery("ALTER TABLE `sub_backups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `sub_backups` CHANGE `naam` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `sub_backups` CHANGE `tekst` `text` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");

        DBConnection::doQuery("RENAME TABLE categorieen TO categories;");
        DBConnection::doQuery("ALTER TABLE `categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `categories` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `categorieid`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;");
        DBConnection::doQuery("ALTER TABLE `categories` CHANGE `naam` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `categories` CHANGE `categorieid` `categoryId` INT(11) NULL DEFAULT NULL;");
        DBConnection::doQuery("ALTER TABLE `categories` CHANGE `alleentitel` `onlyShowTitles` TINYINT(1) NOT NULL DEFAULT '0';");
        DBConnection::doQuery("ALTER TABLE `categories` CHANGE `beschrijving` `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `categories` ADD `showBreadcrumbs` TINYINT(1) NOT NULL DEFAULT '0' AFTER `categoryId`;");

        DBConnection::doQuery("RENAME TABLE fotoboeken TO photoalbums;");
        DBConnection::doQuery("ALTER TABLE `photoalbums` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `photoalbums` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `categorieid`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;");
        DBConnection::doQuery("ALTER TABLE `photoalbums` CHANGE `naam` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `photoalbums` CHANGE `categorieid` `categoryId` INT(11) NULL DEFAULT NULL;");
        DBConnection::doQuery("ALTER TABLE `photoalbums` CHANGE `notities` `notes` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `photoalbums` ADD `showBreadcrumbs` TINYINT(1) NOT NULL DEFAULT '0' AFTER `categoryId`;");

        DBConnection::doQuery("ALTER TABLE `menu` CONVERT TO CHARACTER SET utf8mb4  COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `menu` CHANGE `volgorde` `id` INT(11) NOT NULL AUTO_INCREMENT;");
        DBConnection::doQuery("ALTER TABLE `menu` CHANGE `link` `link` VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `menu` CHANGE `alias` `alias` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `menu` ADD `priority` INT NULL DEFAULT '0' AFTER `isImage`; ");

        DBConnection::doQuery("ALTER TABLE `friendlyurls` CONVERT TO CHARACTER SET utf8mb4  COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `friendlyurls` DROP INDEX `doel`;");
        DBConnection::doQuery("ALTER TABLE `friendlyurls` CHANGE `naam` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `friendlyurls` CHANGE `doel` `target` VARCHAR(750) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `friendlyurls` DROP PRIMARY KEY;");
        DBConnection::doQuery("ALTER TABLE `friendlyurls` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);");
        DBConnection::doQuery("ALTER TABLE `friendlyurls` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `target`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;");
        DBConnection::doQuery("ALTER TABLE `friendlyurls` ADD UNIQUE(`target`); ");

        DBConnection::doQuery("RENAME TABLE mailformulieren TO mailforms;");
        DBConnection::doQuery("ALTER TABLE `mailforms` CONVERT TO CHARACTER SET utf8mb4  COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `mailforms` ADD `sendConfirmation` TINYINT(1) NOT NULL DEFAULT '0' AFTER `antiSpamAnswer`;");
        DBConnection::doQuery("ALTER TABLE `mailforms` ADD `confirmationText` TEXT NULL DEFAULT NULL AFTER `sendConfirmation`;");
        DBConnection::doQuery("ALTER TABLE `mailforms` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `confirmationText`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;");
        DBConnection::doQuery("ALTER TABLE `mailforms` CHANGE `naam` `name` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `mailforms` CHANGE `mailadres` `email` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `mailforms` CHANGE `antispamantwoord` `antiSpamAnswer` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");

        DBConnection::doQuery("RENAME TABLE bijschriften TO photoalbum_captions;");
        DBConnection::doQuery("ALTER TABLE `photoalbum_captions` CONVERT TO CHARACTER SET utf8mb4  COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `photoalbum_captions` DROP PRIMARY KEY;");
        DBConnection::doQuery("ALTER TABLE `photoalbum_captions` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);");
        DBConnection::doQuery("ALTER TABLE `photoalbum_captions` CHANGE `hash` `hash` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `photoalbum_captions` CHANGE `bijschrift` `caption` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");

        DBConnection::doQuery("RENAME TABLE instellingen TO settings;");
        DBConnection::doQuery("ALTER TABLE `settings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `settings` CHANGE `naam` `name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `settings` CHANGE `waarde` `value` VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `settings` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `value`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;");

        DBConnection::doQuery("DELETE FROM `settings` WHERE `name` IN ('menutype', 'facebook_share', 'extra_bodycode');");
        DBConnection::doQuery("UPDATE `settings` SET `name` = 'backgroundColor' WHERE `name` = 'achtergrondkleur'");
        DBConnection::doQuery("UPDATE `settings` SET `name` = 'articleColor' WHERE `name` = 'artikelkleur'");
        DBConnection::doQuery("UPDATE `settings` SET `name` = 'jumboContents' WHERE `name` = 'jumbo_inhoud'");
        DBConnection::doQuery("UPDATE `settings` SET `name` = 'menuBackground' WHERE `name` = 'menuachtergrond'");
        DBConnection::doQuery("UPDATE `settings` SET `name` = 'menuColor' WHERE `name` = 'menukleur'");
        DBConnection::doQuery("UPDATE `settings` SET `name` = 'menuTheme' WHERE `name` = 'menuthema'");
        DBConnection::doQuery("UPDATE `settings` SET `value` = 'dark' WHERE `name` = 'menuTheme' AND `value` = 'donker'");
        DBConnection::doQuery("UPDATE `settings` SET `value` = 'light' WHERE `name` = 'menuTheme' AND `value` = 'licht'");
        DBConnection::doQuery("UPDATE `settings` SET `name` = 'minimumReadLevel' WHERE `name` = 'minimum_niveau_lezen'");
        DBConnection::doQuery("UPDATE `settings` SET `name` = 'subTitle' WHERE `name` = 'ondertitel'");
        DBConnection::doQuery("UPDATE `settings` SET `name` = 'defaultCategory' WHERE `name` = 'standaardcategorie'");
        DBConnection::doQuery("UPDATE `settings` SET `name` = 'frontPageIsJumbo' WHERE `name` = 'voorpagina_is_jumbo'");
        DBConnection::doQuery("UPDATE `settings` SET `name` = 'logo' WHERE `name` = 'websitelogo'");
        DBConnection::doQuery("UPDATE `settings` SET `name` = 'siteName' WHERE `name` = 'websitenaam'");


        DBConnection::doQuery("RENAME TABLE kaartverkoop_concerten TO ticketsale_concerts;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` ADD `numFreeSeats` INT NOT NULL DEFAULT '250' AFTER `gereserveerde_plaatsen_uitverkocht`");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` ADD `numReservedSeats` INT NOT NULL DEFAULT '270' AFTER `numFreeSeats`;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `numReservedSeats`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` CHANGE `naam` `name` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` CHANGE `open_voor_verkoop` `openForSales` TINYINT(1) NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` CHANGE `beschrijving` `description` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` CHANGE `beschrijving_indien_gesloten` `descriptionWhenClosed` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` CHANGE `verzendkosten` `deliveryCost` DOUBLE NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` CHANGE `heeft_gereserveerde_plaatsen` `hasReservedSeats` TINYINT(1) NOT NULL DEFAULT '0';");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` CHANGE `toeslag_gereserveerde_plaats` `reservedSeatCharge` DOUBLE NOT NULL DEFAULT '0';");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` CHANGE `bezorgen_verplicht` `forcedDelivery` TINYINT(1) NOT NULL DEFAULT '0';");
        DBConnection::doQuery("ALTER TABLE `ticketsale_concerts` CHANGE `gereserveerde_plaatsen_uitverkocht` `reservedSeatsAreSoldOut` TINYINT(1) NOT NULL DEFAULT '0';");

        DBConnection::doQuery("RENAME TABLE kaartverkoop_bestellingen TO ticketsale_orders;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `concert_id` `concertId` INT(11) NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `achternaam` `lastName` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `voorletters` `initials` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `e-mailadres` `email` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `straat_en_huisnummer` `street` VARCHAR(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` ADD `houseNumber` INT NOT NULL DEFAULT '0' AFTER `street`; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` ADD `houseNumberAddition` VARCHAR(10) NOT NULL DEFAULT '' AFTER `houseNumber`; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `postcode` `postcode` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `woonplaats` `city` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `thuisbezorgen` `delivery` INT(1) NOT NULL DEFAULT '0'; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `is_bezorgd` `isDelivered` INT(1) NOT NULL DEFAULT '0'; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `gereserveerde_plaatsen` `hasReservedSeats` TINYINT(1) NOT NULL DEFAULT '0'; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `is_betaald` `isPaid` INT(1) NOT NULL DEFAULT '0'; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `ophalen_door_koorlid` `deliveryByMember` TINYINT(1) NOT NULL DEFAULT '0';");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `naam_koorlid` `deliveryMemberName` VARCHAR(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `woont_in_buitenland` `addressIsAbroad` INT(1) NOT NULL DEFAULT '0'; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` CHANGE `opmerkingen` `comments` VARCHAR(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `comments`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;");

        DBConnection::doQuery("RENAME TABLE kaartverkoop_kaartsoorten TO ticketsale_tickettypes;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_tickettypes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_tickettypes` CHANGE `concert_id` `concertId` INT(11) NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_tickettypes` CHANGE `naam` `name` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_tickettypes` CHANGE `prijs` `price` DOUBLE NOT NULL; ");

        DBConnection::doQuery("RENAME TABLE kaartverkoop_bestellingen_kaartsoorten TO ticketsale_orders_tickettypes;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders_tickettypes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders_tickettypes` CHANGE `bestelling_id` `orderId` INT(11) NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders_tickettypes` CHANGE `kaartsoort_id` `tickettypeId` INT(11) NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_orders_tickettypes` CHANGE `aantal` `amount` INT(2) NOT NULL; ");

        DBConnection::doQuery("RENAME TABLE kaartverkoop_gereserveerde_plaatsen TO ticketsale_reservedseats;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_reservedseats` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `ticketsale_reservedseats` CHANGE `bestelling_id` `orderId` INT(11) NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_reservedseats` CHANGE `rij` `row` CHAR(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_reservedseats` CHANGE `eerste_stoel` `firstSeat` INT(3) NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `ticketsale_reservedseats` CHANGE `laatste_stoel` `lastSeat` INT(3) NOT NULL; ");

        DBConnection::doQuery("RENAME TABLE mc_leden TO minecraft_members;");
        DBConnection::doQuery("ALTER TABLE `minecraft_members` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `minecraft_members` DROP PRIMARY KEY;");
        DBConnection::doQuery("ALTER TABLE `minecraft_members` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`); ");
        DBConnection::doQuery("ALTER TABLE `minecraft_members` CHANGE `mcnaam` `userName` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `minecraft_members` CHANGE `echtenaam` `realName` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `minecraft_members` CHANGE `niveau` `level` INT(2) NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `minecraft_members` CHANGE `donateur` `donor` INT(1) NOT NULL DEFAULT '0'; ");
        DBConnection::doQuery("ALTER TABLE `minecraft_members` CHANGE `renderAvatarHaar` `renderAvatarHair` INT(1) NOT NULL DEFAULT '1'; ");
        DBConnection::doQuery('ALTER TABLE `minecraft_members` ADD `newRenderer` BOOLEAN NOT NULL DEFAULT FALSE AFTER `renderAvatarHair`;');
        DBConnection::doQuery('ALTER TABLE `minecraft_members` ADD `uuid` CHAR(32) NULL DEFAULT NULL AFTER `userName`;');
        DBConnection::doQuery('ALTER TABLE `minecraft_members` ADD `skinUrl` CHAR(103) NULL DEFAULT NULL AFTER `donor`;');
        DBConnection::doQuery("ALTER TABLE `minecraft_members` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `newRenderer`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;");

        DBConnection::doQuery("RENAME TABLE mc_servers TO minecraft_servers;");
        DBConnection::doQuery("ALTER TABLE `minecraft_servers` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `minecraft_servers` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `dynmapPort`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;");
        DBConnection::doQuery("ALTER TABLE `minecraft_servers` CHANGE `naam` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");

        DBConnection::doQuery("RENAME TABLE reacties TO sub_replies;");
        DBConnection::doQuery("ALTER TABLE `sub_replies` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DBConnection::doQuery("ALTER TABLE `sub_replies` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`); ");
        DBConnection::doQuery("ALTER TABLE `sub_replies` CHANGE `subid` `subId` INT(11) NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `sub_replies` CHANGE `auteur` `author` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `sub_replies` CHANGE `tekst` `text` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ");
        DBConnection::doQuery("ALTER TABLE `sub_replies` CHANGE `datum` `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP; ");
        DBConnection::doQuery("ALTER TABLE `sub_replies` ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;");


        Setting::set('ticketsale_reservedSeatsDescription',
            'Alle plaatsen in het middenschip van de kerk verkopen wij met een stoelnummer; d.w.z. al deze plaatsen worden
            verkocht als gereserveerde plaats. De stoelnummers lopen van 1 t/m circa %d. Het is een doorlopende reeks,
            dus dit keer geen rijnummer. Aan het einde van een rij verspringt het stoelnummer naar de stoel daarachter.
            De nummers vormen een soort heen en weer gaande slinger door het hele middenschip heen. Het kan dus gebeuren
            dat u een paar kaarten koopt, waarbij de nummering verspringt naar de rij daarachter. Maar wel zo dat de
            stoelen dus direct bij elkaar staan.
            Vrije plaatsen zijn: de zijvakken en de balkons.');

        $frontPage = DBConnection::doQueryAndFetchOne('SELECT link FROM menu WHERE id=(SELECT MIN(id) FROM menu)');
        Setting::set('frontPage', $frontPage);
    }
}

;