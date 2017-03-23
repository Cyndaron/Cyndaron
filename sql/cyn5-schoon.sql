-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 26, 2017 at 10:28 PM
-- Server version: 5.7.17-0ubuntu0.16.04.1
-- PHP Version: 7.0.15-0ubuntu0.16.04.2

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
-- Table structure for table `bijschriften`
--

CREATE TABLE `bijschriften` (
  `hash` varchar(32) NOT NULL,
  `bijschrift` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `categorieen`
--

CREATE TABLE `categorieen` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `alleentitel` tinyint(1) NOT NULL DEFAULT '0',
  `beschrijving` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fotoboeken`
--

CREATE TABLE `fotoboeken` (
  `id` int(3) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `notities` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `friendlyurls`
--

CREATE TABLE `friendlyurls` (
  `naam` varchar(100) NOT NULL,
  `doel` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gebruikers`
--

CREATE TABLE `gebruikers` (
  `id` int(11) NOT NULL,
  `gebruikersnaam` varchar(100) NOT NULL,
  `wachtwoord` varchar(1000) NOT NULL,
  `niveau` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ideeen`
--

CREATE TABLE `ideeen` (
  `id` int(4) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `tekst` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `instellingen`
--

CREATE TABLE `instellingen` (
  `naam` varchar(50) NOT NULL,
  `waarde` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kaartverkoop_bestellingen`
--

CREATE TABLE `kaartverkoop_bestellingen` (
  `id` int(11) NOT NULL,
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
  `opmerkingen` varchar(400) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kaartverkoop_bestellingen_kaartsoorten`
--

CREATE TABLE `kaartverkoop_bestellingen_kaartsoorten` (
  `bestelling_id` int(11) NOT NULL,
  `kaartsoort_id` int(11) NOT NULL,
  `aantal` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kaartverkoop_concerten`
--

CREATE TABLE `kaartverkoop_concerten` (
  `id` int(11) NOT NULL,
  `naam` varchar(200) NOT NULL,
  `open_voor_verkoop` tinyint(1) NOT NULL,
  `beschrijving` mediumtext NOT NULL,
  `beschrijving_indien_gesloten` mediumtext NOT NULL,
  `verzendkosten` double NOT NULL,
  `heeft_gereserveerde_plaatsen` tinyint(1) NOT NULL DEFAULT '0',
  `toeslag_gereserveerde_plaats` int(2) NOT NULL DEFAULT '0',
  `bezorgen_verplicht` tinyint(1) NOT NULL DEFAULT '0',
  `gereserveerde_plaatsen_uitverkocht` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kaartverkoop_gereserveerde_plaatsen`
--

CREATE TABLE `kaartverkoop_gereserveerde_plaatsen` (
  `id` int(11) NOT NULL,
  `bestelling_id` int(11) NOT NULL,
  `rij` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `eerste_stoel` int(3) NOT NULL,
  `laatste_stoel` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kaartverkoop_kaartsoorten`
--

CREATE TABLE `kaartverkoop_kaartsoorten` (
  `id` int(11) NOT NULL,
  `concert_id` int(11) NOT NULL,
  `naam` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `prijs` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mailformulieren`
--

CREATE TABLE `mailformulieren` (
  `id` int(11) NOT NULL,
  `naam` varchar(200) NOT NULL,
  `mailadres` varchar(200) NOT NULL,
  `antispamantwoord` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mc_leden`
--

CREATE TABLE `mc_leden` (
  `mcnaam` varchar(100) NOT NULL,
  `echtenaam` varchar(150) NOT NULL,
  `niveau` int(2) NOT NULL,
  `status` varchar(100) NOT NULL,
  `whovian` int(1) NOT NULL DEFAULT '0',
  `donateur` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `volgorde` int(11) NOT NULL,
  `link` varchar(1000) NOT NULL,
  `alias` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `reacties`
--

CREATE TABLE `reacties` (
  `subid` int(11) NOT NULL,
  `auteur` varchar(100) NOT NULL,
  `tekst` text NOT NULL,
  `datum` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `subs`
--

CREATE TABLE `subs` (
  `id` int(3) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `tekst` text NOT NULL,
  `reacties_aan` int(1) NOT NULL DEFAULT '0',
  `categorieid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vorigeartikelen`
--

CREATE TABLE `vorigeartikelen` (
  `id` int(11) NOT NULL,
  `hid` int(11) NOT NULL,
  `tekst` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vorigesubs`
--

CREATE TABLE `vorigesubs` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `tekst` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bijschriften`
--
ALTER TABLE `bijschriften`
  ADD PRIMARY KEY (`hash`);

--
-- Indexes for table `categorieen`
--
ALTER TABLE `categorieen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fotoboeken`
--
ALTER TABLE `fotoboeken`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `friendlyurls`
--
ALTER TABLE `friendlyurls`
  ADD PRIMARY KEY (`naam`),
  ADD UNIQUE KEY `naam` (`naam`);

--
-- Indexes for table `gebruikers`
--
ALTER TABLE `gebruikers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ideeen`
--
ALTER TABLE `ideeen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `instellingen`
--
ALTER TABLE `instellingen`
  ADD PRIMARY KEY (`naam`);

--
-- Indexes for table `kaartverkoop_bestellingen`
--
ALTER TABLE `kaartverkoop_bestellingen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `concert_id` (`concert_id`);

--
-- Indexes for table `kaartverkoop_bestellingen_kaartsoorten`
--
ALTER TABLE `kaartverkoop_bestellingen_kaartsoorten`
  ADD UNIQUE KEY `bestelling_id` (`bestelling_id`,`kaartsoort_id`),
  ADD KEY `bestelling_id_2` (`bestelling_id`),
  ADD KEY `kaartsoort-id` (`kaartsoort_id`);

--
-- Indexes for table `kaartverkoop_concerten`
--
ALTER TABLE `kaartverkoop_concerten`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kaartverkoop_gereserveerde_plaatsen`
--
ALTER TABLE `kaartverkoop_gereserveerde_plaatsen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bestelling_id` (`bestelling_id`);

--
-- Indexes for table `kaartverkoop_kaartsoorten`
--
ALTER TABLE `kaartverkoop_kaartsoorten`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `concert_id` (`concert_id`,`naam`),
  ADD KEY `concert_id_2` (`concert_id`);

--
-- Indexes for table `mailformulieren`
--
ALTER TABLE `mailformulieren`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mc_leden`
--
ALTER TABLE `mc_leden`
  ADD PRIMARY KEY (`mcnaam`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`volgorde`);

--
-- Indexes for table `reacties`
--
ALTER TABLE `reacties`
  ADD KEY `subid` (`subid`);

--
-- Indexes for table `subs`
--
ALTER TABLE `subs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vorigeartikelen`
--
ALTER TABLE `vorigeartikelen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vorigesubs`
--
ALTER TABLE `vorigesubs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categorieen`
--
ALTER TABLE `categorieen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fotoboeken`
--
ALTER TABLE `fotoboeken`
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `gebruikers`
--
ALTER TABLE `gebruikers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ideeen`
--
ALTER TABLE `ideeen`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `kaartverkoop_bestellingen`
--
ALTER TABLE `kaartverkoop_bestellingen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `kaartverkoop_concerten`
--
ALTER TABLE `kaartverkoop_concerten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `kaartverkoop_gereserveerde_plaatsen`
--
ALTER TABLE `kaartverkoop_gereserveerde_plaatsen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `kaartverkoop_kaartsoorten`
--
ALTER TABLE `kaartverkoop_kaartsoorten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mailformulieren`
--
ALTER TABLE `mailformulieren`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `volgorde` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `subs`
--
ALTER TABLE `subs`
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `kaartverkoop_bestellingen`
--
ALTER TABLE `kaartverkoop_bestellingen`
  ADD CONSTRAINT `kaartverkoop_bestellingen_ibfk_1` FOREIGN KEY (`concert_id`) REFERENCES `kaartverkoop_concerten` (`id`);

--
-- Constraints for table `kaartverkoop_bestellingen_kaartsoorten`
--
ALTER TABLE `kaartverkoop_bestellingen_kaartsoorten`
  ADD CONSTRAINT `bestellings-id` FOREIGN KEY (`bestelling_id`) REFERENCES `kaartverkoop_bestellingen` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `kaartsoort-id` FOREIGN KEY (`kaartsoort_id`) REFERENCES `kaartverkoop_kaartsoorten` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kaartverkoop_gereserveerde_plaatsen`
--
ALTER TABLE `kaartverkoop_gereserveerde_plaatsen`
  ADD CONSTRAINT `kaartverkoop_gereserveerde_plaatsen_ibfk_1` FOREIGN KEY (`bestelling_id`) REFERENCES `kaartverkoop_bestellingen` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kaartverkoop_kaartsoorten`
--
ALTER TABLE `kaartverkoop_kaartsoorten`
  ADD CONSTRAINT `kaartverkoop_kaartsoorten_ibfk_1` FOREIGN KEY (`concert_id`) REFERENCES `kaartverkoop_concerten` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reacties`
--
ALTER TABLE `reacties`
  ADD CONSTRAINT `reacties_ibfk_1` FOREIGN KEY (`subid`) REFERENCES `subs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vorigesubs`
--
ALTER TABLE `vorigesubs`
  ADD CONSTRAINT `vorigesubs_ibfk_1` FOREIGN KEY (`id`) REFERENCES `subs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
