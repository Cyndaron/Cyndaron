CREATE TABLE `registrationsbk_events` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `openForRegistration` tinyint(1) NOT NULL,
    `description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `descriptionWhenClosed` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `registrationCost` double NOT NULL,
    `performedPiece` varchar(200) COLLATE utf8mb4_unicode_ci NULL,
    `termsAndConditions` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `registrationsbk_registrations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `eventId` int(11) NOT NULL,
    `lastName` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
    `initials` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
    `vocalRange` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `email` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
    `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
    `city` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `currentChoir` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `choirExperience` int NOT NULL DEFAULT '0',
    `performedBefore` tinyint(1) NOT NULL DEFAULT '0',
    `approvalStatus` tinyint(1) NOT NULL DEFAULT '0',
    `isPaid` tinyint(1) NOT NULL DEFAULT '0',
    `comments` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `event_id` (`eventId`),
    CONSTRAINT `registrationsbk_registrations_ibfk_1` FOREIGN KEY (`eventId`) REFERENCES `registrationsbk_events` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
