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


CREATE TABLE `category_categories` (
       `id` int NOT NULL,
       `categoryId` int NOT NULL,
       `priority` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `category_categories` ADD FOREIGN KEY (`id`) REFERENCES `categories`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `category_categories` ADD FOREIGN KEY (`categoryId`) REFERENCES `categories`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `category_categories` ADD UNIQUE( `id`, `categoryId`);

CREATE TABLE `sub_categories` (
       `id` int NOT NULL,
       `categoryId` int NOT NULL,
       `priority` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `sub_categories` ADD FOREIGN KEY (`id`) REFERENCES `subs`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `sub_categories` ADD FOREIGN KEY (`categoryId`) REFERENCES `categories`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `sub_categories` ADD UNIQUE( `id`, `categoryId`);

CREATE TABLE `photoalbum_categories` (
      `id` int NOT NULL,
      `categoryId` int NOT NULL,
      `priority` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `photoalbum_categories` ADD FOREIGN KEY (`id`) REFERENCES `photoalbums`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `photoalbum_categories` ADD FOREIGN KEY (`categoryId`) REFERENCES `categories`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `photoalbum_categories` ADD UNIQUE( `id`, `categoryId`);

INSERT INTO category_categories(id,categoryId) SELECT id,categoryId FROM `categories` WHERE categoryId IS NOT NULL AND categoryId <> 0;
INSERT INTO sub_categories(id,categoryId) SELECT id,categoryId FROM `subs` WHERE categoryId IS NOT NULL AND categoryId <> 0;
INSERT INTO photoalbum_categories(id,categoryId) SELECT id,categoryId FROM `photoalbums` WHERE categoryId IS NOT NULL AND categoryId <> 0;

ALTER TABLE categories DROP FOREIGN KEY categories_ibfk_1;
ALTER TABLE `categories` DROP `categoryId`;
ALTER TABLE `subs` DROP `categoryId`;
ALTER TABLE `photoalbums` DROP `categoryId`;

CREATE TABLE `richlink` (
    `id` int NOT NULL,
    `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `previewImage` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `blurb` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
    `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `openInNewTab` tinyint(1) NOT NULL,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `richlink`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `richlink`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

CREATE TABLE `richlink_category` (
     `id` int NOT NULL,
     `categoryId` int NOT NULL,
     `priority` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `richlink_category` ADD FOREIGN KEY (`id`) REFERENCES `richlink`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `richlink_category` ADD FOREIGN KEY (`categoryId`) REFERENCES `categories`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `richlink_category` ADD UNIQUE(`id`, `categoryId`);