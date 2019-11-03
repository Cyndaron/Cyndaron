
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

INSERT INTO settings(name, value) VALUES ('ticketsale_reservedSeatsDescription', 'Alle plaatsen in het middenschip van de kerk verkopen wij met een stoelnummer; d.w.z. al deze plaatsen worden
            verkocht als gereserveerde plaats. De stoelnummers lopen van 1 t/m circa %d. Het is een doorlopende reeks,
            dus dit keer geen rijnummer. Aan het einde van een rij verspringt het stoelnummer naar de stoel daarachter.
            De nummers vormen een soort heen en weer gaande slinger door het hele middenschip heen. Het kan dus gebeuren
            dat u een paar kaarten koopt, waarbij de nummering verspringt naar de rij daarachter. Maar wel zo dat de
            stoelen dus direct bij elkaar staan.
            Vrije plaatsen zijn: de zijvakken en de balkons.'),
                                         ('frontpage', (SELECT link FROM menu WHERE id=(SELECT MIN(id) FROM menu)));