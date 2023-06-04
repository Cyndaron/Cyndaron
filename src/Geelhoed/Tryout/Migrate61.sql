CREATE TABLE `geelhoed_tryout_points`
(
    `id`       int(11) NOT NULL,
    `code`     int(11) NOT NULL,
    `datetime` DATETIME,
    `points` INT(11) NOT NULL,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE `geelhoed_tryout_points`
    ADD PRIMARY KEY (`id`);
