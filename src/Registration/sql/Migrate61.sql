ALTER TABLE `registration_events` ADD `requireApproval` TINYINT(1) NOT NULL AFTER `numSeats`;
ALTER TABLE `registration_events` ADD `hideRegistrationFee` TINYINT(1) NOT NULL AFTER `requireApproval`;
ALTER TABLE `registration_events` ADD `performedPiece` VARCHAR(200) NOT NULL DEFAULT '' AFTER `hideRegistrationFee`;
ALTER TABLE `registration_events` ADD `termsAndConditions` MEDIUMTEXT NOT NULL DEFAULT '' AFTER `performedPiece`;

ALTER TABLE `registration_orders` ADD `approvalStatus` TINYINT(1) NOT NULL DEFAULT 0 AFTER `choirPreference`;
ALTER TABLE `registration_orders` ADD `phone` VARCHAR(20) NOT NULL DEFAULT '' AFTER `approvalStatus`;
ALTER TABLE `registration_orders` ADD `choirExperience` INT NOT NULL DEFAULT 0 AFTER `phone`;
ALTER TABLE `registration_orders` ADD `performedBefore` TINYINT(1) NOT NULL DEFAULT 0 AFTER `choirExperience`;
