DROP TABLE `vorigeartikelen`;
DROP TABLE `ideeen`;

UPDATE `menu` SET link = CONCAT('/', link) WHERE link NOT LIKE 'http%';
UPDATE `friendlyurls` SET doel = CONCAT('/', doel);

UPDATE `menu` SET link = REPLACE(link, '/toonsub.php?id=', '/sub/');
UPDATE `menu` SET link = REPLACE(link, '/tooncategorie.php?id=', '/category/');
UPDATE `menu` SET link = REPLACE(link, '/toonfotoboek.php?id=', '/photoalbum/');
UPDATE `menu` SET link = REPLACE(link, '/mc-leden', '/minecraft/members');
UPDATE `menu` SET link = REPLACE(link, '/mc-status', '/minecraft/status');
UPDATE `menu` SET link = REPLACE(link, '/wieiswie', '/user/gallery');
UPDATE `friendlyurls` SET doel = REPLACE(doel, '/toonsub.php?id=', '/sub/');
UPDATE `friendlyurls` SET doel = REPLACE(doel, '/tooncategorie.php?id=', '/category/');
UPDATE `friendlyurls` SET doel = REPLACE(doel, '/toonfotoboek.php?id=', '/photoalbum/');
UPDATE `friendlyurls` SET doel = REPLACE(doel, '/mc-leden', '/minecraft/members');
UPDATE `friendlyurls` SET doel = REPLACE(doel, '/mc-status', '/minecraft/status');
UPDATE `friendlyurls` SET doel = REPLACE(doel, '/wieiswie', '/user/gallery');

ALTER TABLE `users` ADD `firstName` VARCHAR(100) NOT NULL DEFAULT '' AFTER `level`, ADD `tussenvoegsel` VARCHAR(50) NOT NULL DEFAULT '' AFTER `firstName`, ADD `lastName` VARCHAR(200) NOT NULL DEFAULT '' AFTER `tussenvoegsel`, ADD `role` VARCHAR(100) NOT NULL DEFAULT '' AFTER `lastName`, ADD `comments` VARCHAR(500) NOT NULL DEFAULT '' AFTER `role`, ADD `avatar` VARCHAR(250) NOT NULL DEFAULT '' AFTER `comments`;
ALTER TABLE `users` ADD `hideFromMemberList` TINYINT(1) NOT NULL DEFAULT '0' AFTER `avatar`;
ALTER TABLE `users` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `hideFromMemberList`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;

ALTER TABLE `subs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `subs` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `categorieid`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;
ALTER TABLE `subs` CHANGE `naam` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `subs` CHANGE `tekst` `text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `subs` CHANGE `reacties_aan` `enableComments` INT(1) NOT NULL DEFAULT '0';
ALTER TABLE `subs` CHANGE `categorieid` `categoryId` INT(11) NOT NULL;
ALTER TABLE `subs` ADD `showBreadcrumbs` TINYINT(1) NOT NULL DEFAULT '0' AFTER `categoryId`;
ALTER TABLE `subs` ADD `tags` VARCHAR(750) NOT NULL DEFAULT ''  AFTER `showBreadcrumbs`;
ALTER TABLE `subs` ADD INDEX( `tags`);

RENAME TABLE vorigesubs TO sub_backups;
ALTER TABLE `sub_backups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `sub_backups` CHANGE `naam` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `sub_backups` CHANGE `tekst` `text` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

RENAME TABLE categorieen TO categories;
ALTER TABLE `categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `categories` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `categorieid`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;
ALTER TABLE `categories` CHANGE `naam` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `categories` CHANGE `categorieid` `categoryId` INT(11) NULL DEFAULT NULL;
ALTER TABLE `categories` CHANGE `alleentitel` `viewMode` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `categories` CHANGE `beschrijving` `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `categories` ADD `showBreadcrumbs` TINYINT(1) NOT NULL DEFAULT '0' AFTER `categoryId`;

RENAME TABLE fotoboeken TO photoalbums;
ALTER TABLE `photoalbums` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `photoalbums` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `categorieid`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;
ALTER TABLE `photoalbums` CHANGE `naam` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `photoalbums` CHANGE `categorieid` `categoryId` INT(11) NULL DEFAULT NULL;
ALTER TABLE `photoalbums` CHANGE `notities` `notes` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `photoalbums` ADD `showBreadcrumbs` TINYINT(1) NOT NULL DEFAULT '0' AFTER `categoryId`;

ALTER TABLE `menu` CONVERT TO CHARACTER SET utf8mb4  COLLATE utf8mb4_unicode_ci;
ALTER TABLE `menu` CHANGE `volgorde` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `menu` CHANGE `link` `link` VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `menu` CHANGE `alias` `alias` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `menu` ADD `priority` INT NULL DEFAULT '0' AFTER `isImage`;

ALTER TABLE `friendlyurls` CONVERT TO CHARACTER SET utf8mb4  COLLATE utf8mb4_unicode_ci;
ALTER TABLE `friendlyurls` DROP INDEX `doel`;
ALTER TABLE `friendlyurls` CHANGE `naam` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `friendlyurls` CHANGE `doel` `target` VARCHAR(750) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `friendlyurls` DROP PRIMARY KEY;
ALTER TABLE `friendlyurls` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
ALTER TABLE `friendlyurls` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `target`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;
ALTER TABLE `friendlyurls` ADD UNIQUE(`target`);

RENAME TABLE mailformulieren TO mailforms;
ALTER TABLE `mailforms` CONVERT TO CHARACTER SET utf8mb4  COLLATE utf8mb4_unicode_ci;
ALTER TABLE `mailforms` ADD `sendConfirmation` TINYINT(1) NOT NULL DEFAULT '0' AFTER `antiSpamAnswer`;
ALTER TABLE `mailforms` ADD `confirmationText` TEXT NULL DEFAULT NULL AFTER `sendConfirmation`;
ALTER TABLE `mailforms` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `confirmationText`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;
ALTER TABLE `mailforms` CHANGE `naam` `name` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `mailforms` CHANGE `mailadres` `email` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `mailforms` CHANGE `antispamantwoord` `antiSpamAnswer` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

RENAME TABLE bijschriften TO photoalbum_captions;
ALTER TABLE `photoalbum_captions` CONVERT TO CHARACTER SET utf8mb4  COLLATE utf8mb4_unicode_ci;
ALTER TABLE `photoalbum_captions` DROP PRIMARY KEY;
ALTER TABLE `photoalbum_captions` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
ALTER TABLE `photoalbum_captions` CHANGE `hash` `hash` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `photoalbum_captions` CHANGE `bijschrift` `caption` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

RENAME TABLE instellingen TO settings;
ALTER TABLE `settings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `settings` CHANGE `naam` `name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `settings` CHANGE `waarde` `value` VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `settings` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `value`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;

DELETE FROM `settings` WHERE `name` IN ('menutype', 'facebook_share', 'extra_bodycode');
UPDATE `settings` SET `name` = 'backgroundColor' WHERE `name` = 'achtergrondkleur';
UPDATE `settings` SET `name` = 'articleColor' WHERE `name` = 'artikelkleur';
UPDATE `settings` SET `name` = 'jumboContents' WHERE `name` = 'jumbo_inhoud';
UPDATE `settings` SET `name` = 'menuBackground' WHERE `name` = 'menuachtergrond';
UPDATE `settings` SET `name` = 'menuColor' WHERE `name` = 'menukleur';
UPDATE `settings` SET `name` = 'menuTheme' WHERE `name` = 'menuthema';
UPDATE `settings` SET `value` = 'dark' WHERE `name` = 'menuTheme' AND `value` = 'donker';
UPDATE `settings` SET `value` = 'light' WHERE `name` = 'menuTheme' AND `value` = 'licht';
UPDATE `settings` SET `name` = 'minimumReadLevel' WHERE `name` = 'minimum_niveau_lezen';
UPDATE `settings` SET `name` = 'subTitle' WHERE `name` = 'ondertitel';
UPDATE `settings` SET `name` = 'defaultCategory' WHERE `name` = 'standaardcategorie';
UPDATE `settings` SET `name` = 'frontPageIsJumbo' WHERE `name` = 'voorpagina_is_jumbo';
UPDATE `settings` SET `name` = 'logo' WHERE `name` = 'websitelogo';
UPDATE `settings` SET `name` = 'siteName' WHERE `name` = 'websitenaam';


RENAME TABLE kaartverkoop_concerten TO ticketsale_concerts;
ALTER TABLE `ticketsale_concerts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `ticketsale_concerts` ADD `numFreeSeats` INT NOT NULL DEFAULT '250' AFTER `gereserveerde_plaatsen_uitverkocht`;
ALTER TABLE `ticketsale_concerts` ADD `numReservedSeats` INT NOT NULL DEFAULT '270' AFTER `numFreeSeats`;
ALTER TABLE `ticketsale_concerts` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `numReservedSeats`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;
ALTER TABLE `ticketsale_concerts` CHANGE `naam` `name` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `ticketsale_concerts` CHANGE `open_voor_verkoop` `openForSales` TINYINT(1) NOT NULL;
ALTER TABLE `ticketsale_concerts` CHANGE `beschrijving` `description` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `ticketsale_concerts` CHANGE `beschrijving_indien_gesloten` `descriptionWhenClosed` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `ticketsale_concerts` CHANGE `verzendkosten` `deliveryCost` DOUBLE NOT NULL;
ALTER TABLE `ticketsale_concerts` CHANGE `heeft_gereserveerde_plaatsen` `hasReservedSeats` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `ticketsale_concerts` CHANGE `toeslag_gereserveerde_plaats` `reservedSeatCharge` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `ticketsale_concerts` CHANGE `bezorgen_verplicht` `forcedDelivery` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `ticketsale_concerts` CHANGE `gereserveerde_plaatsen_uitverkocht` `reservedSeatsAreSoldOut` TINYINT(1) NOT NULL DEFAULT '0';

RENAME TABLE kaartverkoop_bestellingen TO ticketsale_orders;
ALTER TABLE `ticketsale_orders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `ticketsale_orders` CHANGE `concert_id` `concertId` INT(11) NOT NULL;
ALTER TABLE `ticketsale_orders` CHANGE `achternaam` `lastName` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `ticketsale_orders` CHANGE `voorletters` `initials` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `ticketsale_orders` CHANGE `e-mailadres` `email` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `ticketsale_orders` CHANGE `straat_en_huisnummer` `street` VARCHAR(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `ticketsale_orders` ADD `houseNumber` INT NOT NULL DEFAULT '0' AFTER `street`;
ALTER TABLE `ticketsale_orders` ADD `houseNumberAddition` VARCHAR(10) NOT NULL DEFAULT '' AFTER `houseNumber`;
ALTER TABLE `ticketsale_orders` CHANGE `postcode` `postcode` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `ticketsale_orders` CHANGE `woonplaats` `city` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `ticketsale_orders` CHANGE `thuisbezorgen` `delivery` INT(1) NOT NULL DEFAULT '0';
ALTER TABLE `ticketsale_orders` CHANGE `is_bezorgd` `isDelivered` INT(1) NOT NULL DEFAULT '0';
ALTER TABLE `ticketsale_orders` CHANGE `gereserveerde_plaatsen` `hasReservedSeats` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `ticketsale_orders` CHANGE `is_betaald` `isPaid` INT(1) NOT NULL DEFAULT '0';
ALTER TABLE `ticketsale_orders` CHANGE `ophalen_door_koorlid` `deliveryByMember` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `ticketsale_orders` CHANGE `naam_koorlid` `deliveryMemberName` VARCHAR(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `ticketsale_orders` CHANGE `woont_in_buitenland` `addressIsAbroad` INT(1) NOT NULL DEFAULT '0';
ALTER TABLE `ticketsale_orders` CHANGE `opmerkingen` `comments` VARCHAR(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `ticketsale_orders` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `comments`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;

RENAME TABLE kaartverkoop_kaartsoorten TO ticketsale_tickettypes;
ALTER TABLE `ticketsale_tickettypes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `ticketsale_tickettypes` CHANGE `concert_id` `concertId` INT(11) NOT NULL;
ALTER TABLE `ticketsale_tickettypes` CHANGE `naam` `name` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `ticketsale_tickettypes` CHANGE `prijs` `price` DOUBLE NOT NULL;

RENAME TABLE kaartverkoop_bestellingen_kaartsoorten TO ticketsale_orders_tickettypes;
ALTER TABLE `ticketsale_orders_tickettypes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `ticketsale_orders_tickettypes` CHANGE `bestelling_id` `orderId` INT(11) NOT NULL;
ALTER TABLE `ticketsale_orders_tickettypes` CHANGE `kaartsoort_id` `tickettypeId` INT(11) NOT NULL;
ALTER TABLE `ticketsale_orders_tickettypes` CHANGE `aantal` `amount` INT(2) NOT NULL;

RENAME TABLE kaartverkoop_gereserveerde_plaatsen TO ticketsale_reservedseats;
ALTER TABLE `ticketsale_reservedseats` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `ticketsale_reservedseats` CHANGE `bestelling_id` `orderId` INT(11) NOT NULL;
ALTER TABLE `ticketsale_reservedseats` CHANGE `rij` `row` CHAR(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `ticketsale_reservedseats` CHANGE `eerste_stoel` `firstSeat` INT(3) NOT NULL;
ALTER TABLE `ticketsale_reservedseats` CHANGE `laatste_stoel` `lastSeat` INT(3) NOT NULL;

RENAME TABLE mc_leden TO minecraft_members;
ALTER TABLE `minecraft_members` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `minecraft_members` DROP PRIMARY KEY;
ALTER TABLE `minecraft_members` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
ALTER TABLE `minecraft_members` CHANGE `mcnaam` `userName` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `minecraft_members` CHANGE `echtenaam` `realName` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `minecraft_members` CHANGE `niveau` `level` INT(2) NOT NULL;
ALTER TABLE `minecraft_members` CHANGE `donateur` `donor` INT(1) NOT NULL DEFAULT '0';
ALTER TABLE `minecraft_members` CHANGE `renderAvatarHaar` `renderAvatarHair` INT(1) NOT NULL DEFAULT '1';
DBConnection::doQuery('ALTER TABLE `minecraft_members` ADD `newRenderer` BOOLEAN NOT NULL DEFAULT FALSE AFTER `renderAvatarHair`;');
DBConnection::doQuery('ALTER TABLE `minecraft_members` ADD `uuid` CHAR(32) NULL DEFAULT NULL AFTER `userName`;');
DBConnection::doQuery('ALTER TABLE `minecraft_members` ADD `skinUrl` CHAR(103) NULL DEFAULT NULL AFTER `donor`;');
ALTER TABLE `minecraft_members` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `newRenderer`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;

RENAME TABLE mc_servers TO minecraft_servers;
ALTER TABLE `minecraft_servers` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `minecraft_servers` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `dynmapPort`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;
ALTER TABLE `minecraft_servers` CHANGE `naam` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

RENAME TABLE reacties TO sub_replies;
ALTER TABLE `sub_replies` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `sub_replies` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
ALTER TABLE `sub_replies` CHANGE `subid` `subId` INT(11) NOT NULL;
ALTER TABLE `sub_replies` CHANGE `auteur` `author` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `sub_replies` CHANGE `tekst` `text` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `sub_replies` CHANGE `datum` `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `sub_replies` ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Table structure for table `registration_events`
--

CREATE TABLE `registration_events` (
    `id` int(11) NOT NULL,
    `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `openForRegistration` tinyint(1) NOT NULL,
    `description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `descriptionWhenClosed` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `registrationCost` double NOT NULL DEFAULT '0',
    `forcedDelivery` tinyint(1) NOT NULL DEFAULT '0',
    `maxRegistrations` int(11) NOT NULL DEFAULT '250',
    `numSeats` int(11) NOT NULL DEFAULT '250',
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_orders`
--

CREATE TABLE `registration_orders` (
    `id` int(11) NOT NULL,
    `eventId` int(11) NOT NULL,
    `lastName` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
    `initials` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
    `vocalRange` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `lunch` tinyint(1) NOT NULL DEFAULT '0',
    `email` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
    `street` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
    `houseNumber` int(11) NOT NULL DEFAULT '0',
    `houseNumberAddition` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `postcode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
    `city` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `isPaid` tinyint(1) NOT NULL DEFAULT '0',
    `comments` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_orders_tickettypes`
--

CREATE TABLE `registration_orders_tickettypes` (
    `orderId` int(11) NOT NULL,
    `tickettypeId` int(11) NOT NULL,
    `amount` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_tickettypes`
--

CREATE TABLE `registration_tickettypes` (
    `id` int(11) NOT NULL,
    `eventId` int(11) NOT NULL,
    `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `price` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `registration_events`
--
ALTER TABLE `registration_events`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registration_orders`
--
ALTER TABLE `registration_orders`
    ADD PRIMARY KEY (`id`),
    ADD KEY `event_id` (`eventId`);

--
-- Indexes for table `registration_orders_tickettypes`
--
ALTER TABLE `registration_orders_tickettypes`
    ADD UNIQUE KEY `bestelling_id` (`orderId`,`tickettypeId`),
    ADD KEY `bestelling_id_2` (`orderId`),
    ADD KEY `kaartsoort-id` (`tickettypeId`);

--
-- Indexes for table `registration_tickettypes`
--
ALTER TABLE `registration_tickettypes`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `event_id` (`eventId`,`name`),
    ADD KEY `event_id_2` (`eventId`);

--
-- AUTO_INCREMENT for table `registration_events`
--
ALTER TABLE `registration_events`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registration_orders`
--
ALTER TABLE `registration_orders`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registration_tickettypes`
--
ALTER TABLE `registration_tickettypes`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `registration_orders`
--
ALTER TABLE `registration_orders`
    ADD CONSTRAINT `registration_orders_ibfk_1` FOREIGN KEY (`eventId`) REFERENCES `registration_events` (`id`);

--
-- Constraints for table `registration_orders_tickettypes`
--
ALTER TABLE `registration_orders_tickettypes`
    ADD CONSTRAINT `bestellings-id_1` FOREIGN KEY (`orderId`) REFERENCES `registration_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `kaartsoort-id_1` FOREIGN KEY (`tickettypeId`) REFERENCES `registration_tickettypes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `registration_tickettypes`
--
ALTER TABLE `registration_tickettypes`
    ADD CONSTRAINT `registration_tickettypes_ibfk_1` FOREIGN KEY (`eventId`) REFERENCES `registration_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;