
RENAME TABLE mc_leden TO minecraft_members;
ALTER TABLE `minecraft_members` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `minecraft_members` DROP PRIMARY KEY;
ALTER TABLE `minecraft_members` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
ALTER TABLE `minecraft_members` CHANGE `mcnaam` `userName` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `minecraft_members` CHANGE `echtenaam` `realName` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `minecraft_members` CHANGE `niveau` `level` INT(2) NOT NULL;
ALTER TABLE `minecraft_members` CHANGE `donateur` `donor` INT(1) NOT NULL DEFAULT '0';
ALTER TABLE `minecraft_members` CHANGE `renderAvatarHaar` `renderAvatarHair` INT(1) NOT NULL DEFAULT '1';
ALTER TABLE `minecraft_members` ADD `newRenderer` BOOLEAN NOT NULL DEFAULT FALSE AFTER `renderAvatarHair`;
ALTER TABLE `minecraft_members` ADD `uuid` CHAR(32) NULL DEFAULT NULL AFTER `userName`;
ALTER TABLE `minecraft_members` ADD `skinUrl` CHAR(103) NULL DEFAULT NULL AFTER `donor`;
ALTER TABLE `minecraft_members` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `newRenderer`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;

RENAME TABLE mc_servers TO minecraft_servers;
ALTER TABLE `minecraft_servers` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `minecraft_servers` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `dynmapPort`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;
ALTER TABLE `minecraft_servers` CHANGE `naam` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
