ALTER TABLE `gebruikers` ADD `email` VARCHAR(255) NULL DEFAULT NULL AFTER `wachtwoord`;
ALTER TABLE `gebruikers` ADD UNIQUE( `email`);

ALTER TABLE `categorieen` ADD `categorieid` INT NULL AFTER `beschrijving`;
ALTER TABLE `categorieen` ADD FOREIGN KEY (`categorieid`) REFERENCES `categorieen`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `menu` ADD `isDropdown` BOOLEAN NOT NULL DEFAULT '0' AFTER `alias`, ADD `isImage` BOOLEAN NOT NULL DEFAULT '0' AFTER `isDropdown`;
# Bestaande menu-items porten
UPDATE `menu` SET link = REPLACE(link, '#dd', ''), isDropdown=1 WHERE link LIKE '%#dd';
UPDATE `menu` SET alias = REPLACE(alias, 'img#', ''), isImage=1 WHERE alias LIKE 'img#%';