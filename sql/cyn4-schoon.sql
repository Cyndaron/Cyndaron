-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 30, 2014 at 10:42 AM
-- Server version: 5.5.31
-- PHP Version: 5.4.31

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `steenbe2_txweb`
--

-- --------------------------------------------------------

--
-- Table structure for table `bijschriften`
--

CREATE TABLE IF NOT EXISTS `bijschriften` (
  `hash` varchar(32) NOT NULL,
  `bijschrift` text NOT NULL,
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `categorieen`
--

CREATE TABLE IF NOT EXISTS `categorieen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `alleentitel` tinyint(1) NOT NULL DEFAULT '0',
  `beschrijving` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fotoboeken`
--

CREATE TABLE IF NOT EXISTS `fotoboeken` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `notities` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `friendlyurls`
--

CREATE TABLE IF NOT EXISTS `friendlyurls` (
  `naam` varchar(100) NOT NULL,
  `doel` varchar(1000) NOT NULL,
  PRIMARY KEY (`naam`),
  UNIQUE KEY `naam` (`naam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gebruikers`
--

CREATE TABLE IF NOT EXISTS `gebruikers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gebruikersnaam` varchar(100) NOT NULL,
  `wachtwoord` varchar(1000) NOT NULL,
  `niveau` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ideeen`
--

CREATE TABLE IF NOT EXISTS `ideeen` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `tekst` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `instellingen`
--

CREATE TABLE IF NOT EXISTS `instellingen` (
  `naam` varchar(50) NOT NULL,
  `waarde` varchar(1000) NOT NULL,
  PRIMARY KEY (`naam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mailformulieren`
--

CREATE TABLE IF NOT EXISTS `mailformulieren` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(200) NOT NULL,
  `mailadres` varchar(200) NOT NULL,
  `antispamantwoord` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `volgorde` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(1000) NOT NULL,
  `alias` varchar(100) NOT NULL,
  PRIMARY KEY (`volgorde`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `reacties`
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
-- Table structure for table `subs`
--

CREATE TABLE IF NOT EXISTS `subs` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `tekst` text NOT NULL,
  `reacties_aan` int(1) NOT NULL DEFAULT '0',
  `categorieid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vorigeartikelen`
--

CREATE TABLE IF NOT EXISTS `vorigeartikelen` (
  `id` int(11) NOT NULL,
  `hid` int(11) NOT NULL,
  `tekst` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vorigesubs`
--

CREATE TABLE IF NOT EXISTS `vorigesubs` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `tekst` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

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
