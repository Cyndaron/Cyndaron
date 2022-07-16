ALTER TABLE `ticketsale_orders` ADD `transactionCode` VARCHAR(32) NULL DEFAULT NULL AFTER `addressIsAbroad`, ADD `secretCode` VARCHAR(32) NULL DEFAULT NULL AFTER `transactionCode`;
ALTER TABLE `ticketsale_orders` ADD UNIQUE(`transactionCode`), ADD UNIQUE (`secretCode`);

ALTER TABLE `ticketsale_orders_tickettypes` DROP INDEX `bestelling_id`;
ALTER TABLE `ticketsale_orders_tickettypes` ADD `secretCode` VARCHAR(32) NULL AFTER `amount`, ADD UNIQUE `TOTT_SC_UNIQUE` (`secretCode`);

ALTER TABLE `ticketsale_concerts` ADD `digitalDelivery` BOOLEAN NOT NULL DEFAULT FALSE AFTER `forcedDelivery`;
