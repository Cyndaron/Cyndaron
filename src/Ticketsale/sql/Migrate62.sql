ALTER TABLE `ticketsale_orders` ADD `transactionCode` VARCHAR(32) NULL DEFAULT NULL AFTER `addressIsAbroad`, ADD `secretCode` VARCHAR(32) NULL DEFAULT NULL AFTER `transactionCode`;
ALTER TABLE `ticketsale_orders` ADD UNIQUE(`transactionCode`), ADD UNIQUE (`secretCode`);

ALTER TABLE `ticketsale_orders_tickettypes` DROP INDEX `bestelling_id`;
ALTER TABLE `ticketsale_orders_tickettypes` ADD `secretCode` VARCHAR(32) NULL AFTER `amount`, ADD UNIQUE `TOTT_SC_UNIQUE` (`secretCode`);
ALTER TABLE `ticketsale_orders_tickettypes` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);

ALTER TABLE `ticketsale_concerts` ADD `digitalDelivery` BOOLEAN NOT NULL DEFAULT FALSE AFTER `forcedDelivery`;
ALTER TABLE `ticketsale_concerts` ADD `secretCode` VARCHAR(32) NOT NULL AFTER `deliveryCostInterface`, ADD UNIQUE `TC_SC_UNIQUE` (`secretCode`);
ALTER TABLE `ticketsale_concerts` ADD `date` TIMESTAMP NOT NULL AFTER `secretCode`;
ALTER TABLE `ticketsale_concerts` ADD `ticketInfo` TEXT NOT NULL AFTER `date`;
ALTER TABLE `ticketsale_concerts` ADD `location` VARCHAR(255) NOT NULL AFTER `date`;

ALTER TABLE `ticketsale_orders_tickettypes` ADD `hasBeenScanned` BOOLEAN NOT NULL DEFAULT FALSE AFTER `secretCode`;
