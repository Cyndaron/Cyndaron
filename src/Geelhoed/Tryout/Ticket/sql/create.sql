CREATE TABLE IF NOT EXISTS `geelhoed_tryout_tickettype`
(
    `id`         INT AUTO_INCREMENT NOT NULL,
    `name`       VARCHAR(100) NOT NULL,
    `annotation` VARCHAR(100) NOT NULL,
    `price`      DOUBLE NOT NULL ,
    `created`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE `geelhoed_tryout_tickettype`
    ADD PRIMARY KEY (`id`);

CREATE TABLE IF NOT EXISTS `geelhoed_tryout_order`
(
    `id`              INT AUTO_INCREMENT NOT NULL,
    `tryoutId`        INT NOT NULL,
    `name`            VARCHAR(100) NOT NULL,
    `email`           VARCHAR(150) NOT NULL,
    `isPaid`          BOOLEAN NOT NULL DEFAULT 0,
    `transactionCode` VARCHAR(20) NOT NULL DEFAULT '',
    `created`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE `geelhoed_tryout_order`
    ADD PRIMARY KEY (`id`),
    ADD CONSTRAINT `geelhoed_tryout_order_1` FOREIGN KEY (`tryoutId`) REFERENCES `geelhoed_volunteer_tot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `geelhoed_tryout_order_tickettype`
(
    `id`       INT AUTO_INCREMENT NOT NULL,
    `orderId`  INT NOT NULL,
    `typeId`   INT NOT NULL,
    `amount`   INT NOT NULL DEFAULT 0,
    `created`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE `geelhoed_tryout_order_tickettype`
    ADD PRIMARY KEY (`id`),
    ADD CONSTRAINT `geelhoed_tryout_order_tickettype_1` FOREIGN KEY (`orderId`) REFERENCES `geelhoed_tryout_order` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `geelhoed_tryout_order_tickettype_2` FOREIGN KEY (`typeId`) REFERENCES `geelhoed_tryout_tickettype` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
