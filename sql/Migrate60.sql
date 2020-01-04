# Make sure that Migrate53 has been executed first.

RENAME TABLE gebruikers TO users;
ALTER TABLE `users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `users` CHANGE `gebruikersnaam` `username` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `users` CHANGE `wachtwoord` `password` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `users` CHANGE `niveau` `level` INT(1) NOT NULL;
ALTER TABLE `users`
    ADD `firstName` VARCHAR(100) NOT NULL DEFAULT '' AFTER `level`,
    ADD `tussenvoegsel` VARCHAR(50) NOT NULL DEFAULT '' AFTER `firstName`,
    ADD `lastName` VARCHAR(200) NOT NULL DEFAULT '' AFTER `tussenvoegsel`,
    ADD `role` VARCHAR(100) NOT NULL DEFAULT '' AFTER `lastName`,
    ADD `comments` VARCHAR(500) NOT NULL DEFAULT '' AFTER `role`,
    ADD `avatar` VARCHAR(250) NOT NULL DEFAULT '' AFTER `comments`,
    ADD `hideFromMemberList` TINYINT(1) NOT NULL DEFAULT '0' AFTER `avatar`,
    ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `hideFromMemberList`,
    ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;


DROP TABLE IF EXISTS `vorigeartikelen`;
DROP TABLE IF EXISTS `ideeen`;

UPDATE `menu` SET link = CONCAT('/', link) WHERE link NOT LIKE 'http%';
UPDATE `menu` SET link = REPLACE(link, '/toonsub.php?id=', '/sub/');
UPDATE `menu` SET link = REPLACE(link, '/tooncategorie.php?id=', '/category/');
UPDATE `menu` SET link = REPLACE(link, '/toonfotoboek.php?id=', '/photoalbum/');
UPDATE `menu` SET link = REPLACE(link, '/mc-leden', '/minecraft/members');
UPDATE `menu` SET link = REPLACE(link, '/mc-status', '/minecraft/status');
UPDATE `menu` SET link = REPLACE(link, '/wieiswie', '/user/gallery');

UPDATE `friendlyurls` SET doel = CONCAT('/', doel);
UPDATE `friendlyurls` SET doel = REPLACE(doel, '/toonsub.php?id=', '/sub/');
UPDATE `friendlyurls` SET doel = REPLACE(doel, '/tooncategorie.php?id=', '/category/');
UPDATE `friendlyurls` SET doel = REPLACE(doel, '/toonfotoboek.php?id=', '/photoalbum/');
UPDATE `friendlyurls` SET doel = REPLACE(doel, '/mc-leden', '/minecraft/members');
UPDATE `friendlyurls` SET doel = REPLACE(doel, '/mc-status', '/minecraft/status');
UPDATE `friendlyurls` SET doel = REPLACE(doel, '/wieiswie', '/user/gallery');

ALTER TABLE `subs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `subs` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `categorieid`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;
ALTER TABLE `subs` CHANGE `naam` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `subs` CHANGE `tekst` `text` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
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
ALTER TABLE `photoalbums` ADD `hideFromOverview` TINYINT(1) NOT NULL DEFAULT '0' AFTER `showBreadcrumbs`, ADD `viewMode` TINYINT(1) NOT NULL DEFAULT '0' AFTER `hideFromOverview`;

ALTER TABLE `menu` CONVERT TO CHARACTER SET utf8mb4  COLLATE utf8mb4_unicode_ci;
ALTER TABLE `menu` CHANGE `volgorde` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `menu` CHANGE `link` `link` VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `menu` CHANGE `alias` `alias` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `menu` ADD `priority` INT NULL DEFAULT '0' AFTER `isImage`;
ALTER TABLE `menu` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `priority`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;

ALTER TABLE `friendlyurls` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `friendlyurls` DROP INDEX IF EXISTS `doel`;
ALTER TABLE `friendlyurls` CHANGE `naam` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `friendlyurls` CHANGE `doel` `target` VARCHAR(750) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `friendlyurls` DROP PRIMARY KEY;
ALTER TABLE `friendlyurls` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
ALTER TABLE `friendlyurls` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `target`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;
ALTER TABLE `friendlyurls` ADD UNIQUE(`target`);

RENAME TABLE mailformulieren TO mailforms;
ALTER TABLE `mailforms` CONVERT TO CHARACTER SET utf8mb4  COLLATE utf8mb4_unicode_ci;
ALTER TABLE `mailforms` CHANGE `naam` `name` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `mailforms` CHANGE `mailadres` `email` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `mailforms` CHANGE `antispamantwoord` `antiSpamAnswer` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `mailforms` ADD `sendConfirmation` TINYINT(1) NOT NULL DEFAULT '0' AFTER `antiSpamAnswer`;
ALTER TABLE `mailforms` ADD `confirmationText` TEXT NULL DEFAULT NULL AFTER `sendConfirmation`;
ALTER TABLE `mailforms` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `confirmationText`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;

RENAME TABLE bijschriften TO photoalbum_captions;
ALTER TABLE `photoalbum_captions` CONVERT TO CHARACTER SET utf8mb4  COLLATE utf8mb4_unicode_ci;
ALTER TABLE `photoalbum_captions` DROP PRIMARY KEY;
ALTER TABLE `photoalbum_captions` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
ALTER TABLE `photoalbum_captions` CHANGE `hash` `hash` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `photoalbum_captions` CHANGE `bijschrift` `caption` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `photoalbum_captions` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `caption`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;


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

RENAME TABLE reacties TO sub_replies;
ALTER TABLE `sub_replies` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `sub_replies` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
ALTER TABLE `sub_replies` CHANGE `subid` `subId` INT(11) NOT NULL;
ALTER TABLE `sub_replies` CHANGE `auteur` `author` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `sub_replies` CHANGE `tekst` `text` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `sub_replies` CHANGE `datum` `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `sub_replies` ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;
