ALTER TABLE `ticketsale_orders` ADD `transactionCode` VARCHAR(32) NULL DEFAULT NULL AFTER `addressIsAbroad`, ADD `secretCode` VARCHAR(32) NULL DEFAULT NULL AFTER `transactionCode`;
ALTER TABLE `ticketsale_orders` ADD UNIQUE(`transactionCode`), ADD UNIQUE (`secretCode`);
