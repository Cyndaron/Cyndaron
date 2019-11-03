SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Table structure for table `registration_events`
--

CREATE TABLE `registration_events` (
    `id` int(11) NOT NULL,
    `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `openForRegistration` tinyint(1) NOT NULL,
    `description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `descriptionWhenClosed` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `registrationCost0` double NOT NULL,
    `registrationCost1` double NOT NULL,
    `lunchCost` double NOT NULL,
    `maxRegistrations` int(11) NOT NULL DEFAULT '250',
    `numSeats` int(11) NOT NULL DEFAULT '250',
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_orders`
--

CREATE TABLE `registration_orders` (
    `id` int(11) NOT NULL,
    `eventId` int(11) NOT NULL,
    `lastName` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
    `initials` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
    `registrationGroup` tinyint(1) NOT NULL DEFAULT '0',
    `vocalRange` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `birthYear` int(4) DEFAULT NULL,
    `email` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
    `street` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
    `houseNumber` int(11) NOT NULL DEFAULT '0',
    `houseNumberAddition` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `postcode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
    `city` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `isPaid` int(1) NOT NULL DEFAULT '0',
    `lunch` tinyint(1) NOT NULL DEFAULT '0',
    `lunchType` varchar(200) NOT NULL DEFAULT '',
    `bhv` tinyint(1) NOT NULL DEFAULT '0',
    `kleinkoor` tinyint(1) NOT NULL DEFAULT '0',
    `kleinkoorExplanation` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `participatedBefore` tinyint(1) NOT NULL DEFAULT '0',
    `numPosters` int(2) NOT NULL DEFAULT '0',
    `comments` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_orders_tickettypes`
--

CREATE TABLE `registration_orders_tickettypes` (
    `orderId` int(11) NOT NULL,
    `tickettypeId` int(11) NOT NULL,
    `amount` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_tickettypes`
--

CREATE TABLE `registration_tickettypes` (
    `id` int(11) NOT NULL,
    `eventId` int(11) NOT NULL,
    `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `price` double NOT NULL,
    `discountPer5` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `registration_events`
--
ALTER TABLE `registration_events`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registration_orders`
--
ALTER TABLE `registration_orders`
    ADD PRIMARY KEY (`id`),
    ADD KEY `event_id` (`eventId`);

--
-- Indexes for table `registration_orders_tickettypes`
--
ALTER TABLE `registration_orders_tickettypes`
    ADD UNIQUE KEY `bestelling_id` (`orderId`,`tickettypeId`),
    ADD KEY `bestelling_id_2` (`orderId`),
    ADD KEY `kaartsoort-id` (`tickettypeId`);

--
-- Indexes for table `registration_tickettypes`
--
ALTER TABLE `registration_tickettypes`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `event_id` (`eventId`,`name`),
    ADD KEY `event_id_2` (`eventId`);

--
-- AUTO_INCREMENT for table `registration_events`
--
ALTER TABLE `registration_events`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registration_orders`
--
ALTER TABLE `registration_orders`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registration_tickettypes`
--
ALTER TABLE `registration_tickettypes`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registration_orders`
--
ALTER TABLE `registration_orders`
    ADD CONSTRAINT `registration_orders_ibfk_1` FOREIGN KEY (`eventId`) REFERENCES `registration_events` (`id`);

--
-- Constraints for table `registration_orders_tickettypes`
--
ALTER TABLE `registration_orders_tickettypes`
    ADD CONSTRAINT `bestellings-id_1` FOREIGN KEY (`orderId`) REFERENCES `registration_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `kaartsoort-id_1` FOREIGN KEY (`tickettypeId`) REFERENCES `registration_tickettypes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `registration_tickettypes`
--
ALTER TABLE `registration_tickettypes`
    ADD CONSTRAINT `registration_tickettypes_ibfk_1` FOREIGN KEY (`eventId`) REFERENCES `registration_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
