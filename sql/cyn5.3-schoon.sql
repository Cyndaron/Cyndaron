-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Gegenereerd op: 12 jan 2019 om 12:44
-- Serverversie: 5.7.24-0ubuntu0.18.04.1
-- PHP-versie: 7.2.10-0ubuntu0.18.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
-- Tabelstructuur voor tabel `bijschriften`
--

CREATE TABLE IF NOT EXISTS `bijschriften` (
  `hash` varchar(32) NOT NULL,
  `bijschrift` text NOT NULL,
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `categorieen`
--

CREATE TABLE IF NOT EXISTS `categorieen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `alleentitel` tinyint(1) NOT NULL DEFAULT '0',
  `beschrijving` text NOT NULL,
  `categorieid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categorieen_ibfk_1` (`categorieid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fotoboeken`
--

CREATE TABLE IF NOT EXISTS `fotoboeken` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `notities` text NOT NULL,
  `categorieid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categorieid` (`categorieid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `friendlyurls`
--

CREATE TABLE IF NOT EXISTS `friendlyurls` (
  `naam` varchar(100) NOT NULL,
  `doel` varchar(1000) NOT NULL,
  PRIMARY KEY (`naam`),
  UNIQUE KEY `naam` (`naam`),
  KEY `doel` (`doel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `gebruikers`
--

CREATE TABLE IF NOT EXISTS `gebruikers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gebruikersnaam` varchar(100) NOT NULL,
  `wachtwoord` varchar(1000) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `niveau` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ideeen`
--

CREATE TABLE IF NOT EXISTS `ideeen` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `tekst` text NOT NULL,
  `datum` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `instellingen`
--

CREATE TABLE IF NOT EXISTS `instellingen` (
  `naam` varchar(50) NOT NULL,
  `waarde` varchar(1000) NOT NULL,
  PRIMARY KEY (`naam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `kaartverkoop_bestellingen`
--

CREATE TABLE IF NOT EXISTS `kaartverkoop_bestellingen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `concert_id` int(11) NOT NULL,
  `achternaam` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `voorletters` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `e-mailadres` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `straat_en_huisnummer` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `postcode` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `woonplaats` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `thuisbezorgen` int(1) NOT NULL DEFAULT '0',
  `is_bezorgd` int(1) NOT NULL DEFAULT '0',
  `gereserveerde_plaatsen` tinyint(1) NOT NULL DEFAULT '0',
  `is_betaald` int(1) NOT NULL DEFAULT '0',
  `ophalen_door_koorlid` tinyint(1) NOT NULL DEFAULT '0',
  `naam_koorlid` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `woont_in_buitenland` int(1) NOT NULL DEFAULT '0',
  `opmerkingen` varchar(400) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `concert_id` (`concert_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `kaartverkoop_bestellingen_kaartsoorten`
--

CREATE TABLE IF NOT EXISTS `kaartverkoop_bestellingen_kaartsoorten` (
  `bestelling_id` int(11) NOT NULL,
  `kaartsoort_id` int(11) NOT NULL,
  `aantal` int(2) NOT NULL,
  UNIQUE KEY `bestelling_id` (`bestelling_id`,`kaartsoort_id`),
  KEY `bestelling_id_2` (`bestelling_id`),
  KEY `kaartsoort-id` (`kaartsoort_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `kaartverkoop_concerten`
--

CREATE TABLE IF NOT EXISTS `kaartverkoop_concerten` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(200) NOT NULL,
  `open_voor_verkoop` tinyint(1) NOT NULL,
  `beschrijving` mediumtext NOT NULL,
  `beschrijving_indien_gesloten` mediumtext NOT NULL,
  `verzendkosten` double NOT NULL,
  `heeft_gereserveerde_plaatsen` tinyint(1) NOT NULL DEFAULT '0',
  `toeslag_gereserveerde_plaats` int(2) NOT NULL DEFAULT '0',
  `bezorgen_verplicht` tinyint(1) NOT NULL DEFAULT '0',
  `gereserveerde_plaatsen_uitverkocht` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `kaartverkoop_gereserveerde_plaatsen`
--

CREATE TABLE IF NOT EXISTS `kaartverkoop_gereserveerde_plaatsen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bestelling_id` int(11) NOT NULL,
  `rij` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `eerste_stoel` int(3) NOT NULL,
  `laatste_stoel` int(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bestelling_id` (`bestelling_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `kaartverkoop_kaartsoorten`
--

CREATE TABLE IF NOT EXISTS `kaartverkoop_kaartsoorten` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `concert_id` int(11) NOT NULL,
  `naam` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `prijs` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `concert_id` (`concert_id`,`naam`),
  KEY `concert_id_2` (`concert_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mailformulieren`
--

CREATE TABLE IF NOT EXISTS `mailformulieren` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(200) NOT NULL,
  `mailadres` varchar(200) NOT NULL,
  `antispamantwoord` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mc_leden`
--

CREATE TABLE IF NOT EXISTS `mc_leden` (
  `mcnaam` varchar(100) NOT NULL,
  `echtenaam` varchar(150) NOT NULL,
  `niveau` int(2) NOT NULL,
  `status` varchar(100) NOT NULL,
  `donateur` int(1) NOT NULL DEFAULT '0',
  `renderAvatarHaar` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`mcnaam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mc_servers`
--

CREATE TABLE IF NOT EXISTS `mc_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hostname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `port` int(11) NOT NULL,
  `dynmapPort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `volgorde` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(200) NOT NULL,
  `alias` varchar(100) NOT NULL,
  `isDropdown` tinyint(1) NOT NULL DEFAULT '0',
  `isImage` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`volgorde`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `mutaties`
--

CREATE TABLE IF NOT EXISTS `mutaties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rekening` varchar(5) NOT NULL,
  `code` varchar(10) NOT NULL,
  `datum` date NOT NULL,
  `commentaar` varchar(100) NOT NULL,
  `bij` double NOT NULL DEFAULT '0',
  `af` double NOT NULL DEFAULT '0',
  `btw` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `rekening` (`rekening`),
  KEY `datum` (`datum`),
  KEY `rekening_2` (`rekening`,`datum`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `reacties`
--

CREATE TABLE IF NOT EXISTS `reacties` (
  `subid` int(11) NOT NULL,
  `auteur` varchar(100) NOT NULL,
  `tekst` text NOT NULL,
  `datum` datetime NOT NULL,
  KEY `subid` (`subid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `subs`
--

CREATE TABLE IF NOT EXISTS `subs` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `tekst` text NOT NULL,
  `reacties_aan` int(1) NOT NULL DEFAULT '0',
  `categorieid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `categorieid` (`categorieid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `vorigesubs`
--

CREATE TABLE IF NOT EXISTS `vorigesubs` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `tekst` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `categorieen`
--
ALTER TABLE `categorieen`
  ADD CONSTRAINT `categorieen_ibfk_1` FOREIGN KEY (`categorieid`) REFERENCES `categorieen` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Beperkingen voor tabel `kaartverkoop_bestellingen`
--
ALTER TABLE `kaartverkoop_bestellingen`
  ADD CONSTRAINT `kaartverkoop_bestellingen_ibfk_1` FOREIGN KEY (`concert_id`) REFERENCES `kaartverkoop_concerten` (`id`);

--
-- Beperkingen voor tabel `kaartverkoop_bestellingen_kaartsoorten`
--
ALTER TABLE `kaartverkoop_bestellingen_kaartsoorten`
  ADD CONSTRAINT `bestellings-id` FOREIGN KEY (`bestelling_id`) REFERENCES `kaartverkoop_bestellingen` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `kaartsoort-id` FOREIGN KEY (`kaartsoort_id`) REFERENCES `kaartverkoop_kaartsoorten` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Beperkingen voor tabel `kaartverkoop_gereserveerde_plaatsen`
--
ALTER TABLE `kaartverkoop_gereserveerde_plaatsen`
  ADD CONSTRAINT `kaartverkoop_gereserveerde_plaatsen_ibfk_1` FOREIGN KEY (`bestelling_id`) REFERENCES `kaartverkoop_bestellingen` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Beperkingen voor tabel `kaartverkoop_kaartsoorten`
--
ALTER TABLE `kaartverkoop_kaartsoorten`
  ADD CONSTRAINT `kaartverkoop_kaartsoorten_ibfk_1` FOREIGN KEY (`concert_id`) REFERENCES `kaartverkoop_concerten` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Beperkingen voor tabel `reacties`
--
ALTER TABLE `reacties`
  ADD CONSTRAINT `reacties_ibfk_1` FOREIGN KEY (`subid`) REFERENCES `subs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Beperkingen voor tabel `vorigesubs`
--
ALTER TABLE `vorigesubs`
  ADD CONSTRAINT `vorigesubs_ibfk_1` FOREIGN KEY (`id`) REFERENCES `subs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
