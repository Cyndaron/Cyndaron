ALTER TABLE `ticketsale_concerts` ADD `deliveryCostInterface` VARCHAR(255) NOT NULL DEFAULT '' AFTER `numReservedSeats`;
ALTER TABLE cyndaron.`ticketsale_tickettypes` ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `price`, ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;
ALTER TABLE `ticketsale_orders` ADD `additionalData` TEXT NOT NULL AFTER `comments`;
