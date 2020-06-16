SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `user_rights` (
                               `userId` int NOT NULL,
                               `right` varchar(63) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `user_rights`
    ADD UNIQUE KEY `userId` (`userId`,`right`);

ALTER TABLE `user_rights`
    ADD CONSTRAINT `user_rights_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

ALTER TABLE `categories` ADD `image` VARCHAR(100) NOT NULL AFTER `name`, ADD `blurb` VARCHAR(400) NOT NULL AFTER `image`;
ALTER TABLE `subs` ADD `image` VARCHAR(100) NOT NULL AFTER `name`, ADD `blurb` VARCHAR(400) NOT NULL AFTER `image`;
ALTER TABLE `photoalbums` ADD `image` VARCHAR(100) NOT NULL AFTER `name`, ADD `blurb` VARCHAR(400) NOT NULL AFTER `image`;

ALTER TABLE `categories` ADD `previewImage` VARCHAR(100) NOT NULL AFTER `image`;
ALTER TABLE `subs` ADD `previewImage` VARCHAR(100) NOT NULL AFTER `image`;
ALTER TABLE `photoalbums` ADD `previewImage` VARCHAR(100) NOT NULL AFTER `image`;