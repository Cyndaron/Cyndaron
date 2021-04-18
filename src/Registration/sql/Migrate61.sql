ALTER TABLE `registration_events` ADD `requireApproval` TINYINT(1) NOT NULL AFTER `numSeats`;
ALTER TABLE `registration_events` ADD `hideRegistrationFee` TINYINT(1) NOT NULL AFTER `requireApproval`;
