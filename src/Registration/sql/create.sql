SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cyndaron`
--

-- --------------------------------------------------------

--
-- Table structure for table `registration_events`
--

CREATE TABLE `registration_events` (
                                       `id` int NOT NULL,
                                       `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `openForRegistration` tinyint(1) NOT NULL,
                                       `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `descriptionWhenClosed` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `registrationCost0` double NOT NULL,
                                       `registrationCost1` double NOT NULL,
                                       `registrationCost2` double NOT NULL,
                                       `lunchCost` double NOT NULL,
                                       `maxRegistrations` int NOT NULL DEFAULT '250',
                                       `numSeats` int NOT NULL DEFAULT '250',
                                       `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                       `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_orders`
--

CREATE TABLE `registration_orders` (
                                       `id` int NOT NULL,
                                       `eventId` int NOT NULL,
                                       `lastName` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `initials` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `registrationGroup` tinyint(1) NOT NULL DEFAULT '0',
                                       `vocalRange` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `birthYear` int DEFAULT NULL,
                                       `email` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `street` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `houseNumber` int NOT NULL DEFAULT '0',
                                       `houseNumberAddition` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                                       `postcode` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `city` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `isPaid` int NOT NULL DEFAULT '0',
                                       `lunch` tinyint(1) NOT NULL DEFAULT '0',
                                       `lunchType` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                                       `bhv` tinyint(1) NOT NULL DEFAULT '0',
                                       `kleinkoor` tinyint(1) NOT NULL DEFAULT '0',
                                       `kleinkoorExplanation` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                                       `participatedBefore` tinyint(1) NOT NULL DEFAULT '0',
                                       `numPosters` int NOT NULL DEFAULT '0',
                                       `currentChoir` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `choirPreference` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `comments` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                       `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_orders_tickettypes`
--

CREATE TABLE `registration_orders_tickettypes` (
                                                   `orderId` int NOT NULL,
                                                   `tickettypeId` int NOT NULL,
                                                   `amount` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_tickettypes`
--

CREATE TABLE `registration_tickettypes` (
                                            `id` int NOT NULL,
                                            `eventId` int NOT NULL,
                                            `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `price` double NOT NULL,
                                            `discountPer5` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `registration_events`
--
ALTER TABLE `registration_events`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registration_orders`
--
ALTER TABLE `registration_orders`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registration_tickettypes`
--
ALTER TABLE `registration_tickettypes`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


/*


 ALTER TABLE `registration_orders` ADD `currentChoir` VARCHAR(120) NOT NULL AFTER `numPosters`;
 ALTER TABLE `registration_orders` ADD `choirPreference` VARCHAR(50) NOT NULL AFTER `currentChoir`;
 */
ALTER TABLE `registration_orders` ADD `approvalStatus` tinyint(1) NOT NULL DEFAULT '0' AFTER `choirPreference`;

ALTER TABLE `registration_events` ADD `requireApproval` TINYINT(1) NOT NULL AFTER `numSeats`;

/* Ported from SBK */
ALTER TABLE `registration_events` ADD `performedPiece` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' AFTER `requireApproval`;
ALTER TABLE `registration_events` ADD `termsAndConditions` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL AFTER `performedPiece`;

ALTER TABLE `registration_orders` ADD `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL AFTER email;
ALTER TABLE `registration_orders` ADD `choirExperience` int NOT NULL DEFAULT '0' AFTER `choirPreference`;
ALTER TABLE `registration_orders` ADD `performedBefore` tinyint(1) NOT NULL DEFAULT '0' AFTER `choirExperience`;
