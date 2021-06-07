CREATE TABLE `minecraft_members` (
     `id` int(11) NOT NULL,
     `userName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
     `uuid` char(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
     `realName` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
     `level` int(2) NOT NULL,
     `status` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
     `donor` int(1) NOT NULL DEFAULT '0',
     `skinUrl` char(103) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
     `renderAvatarHair` int(1) NOT NULL DEFAULT '1',
     `newRenderer` tinyint(1) NOT NULL DEFAULT '0',
     `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `minecraft_servers` (
     `id` int(11) NOT NULL,
     `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
     `hostname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
     `port` int(11) NOT NULL,
     `dynmapPort` int(11) NOT NULL,
     `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
