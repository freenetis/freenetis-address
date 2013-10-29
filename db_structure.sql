/*
 * This file is part of open source system FreenetIS
 * and it is released under GPLv3 licence.
 * 
 * More info about licence can be found:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * More info about project can be found:
 * http://www.freenetis.org/
 * 
 */

CREATE DATABASE `addresses` DEFAULT CHARACTER SET utf8 COLLATE utf8_czech_ci;

CREATE TABLE IF NOT EXISTS `addresses` (
  `country` int(11) NOT NULL,
  `town_name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `town_quarter` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `district_name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `street` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `number` varchar(15) COLLATE utf8_czech_ci NOT NULL,
  `zip_code` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  UNIQUE KEY `FULLTEXT` (`town_name`,`town_quarter`,`district_name`,`street`,`number`,`zip_code`,`country`),
  KEY `country` (`country`),
  FULLTEXT KEY `town_name` (`town_name`),
  FULLTEXT KEY `town_quarter` (`town_quarter`),
  FULLTEXT KEY `district_name` (`district_name`),
  FULLTEXT KEY `street` (`street`),
  FULLTEXT KEY `number` (`number`),
  FULLTEXT KEY `zip_code` (`zip_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE IF NOT EXISTS `addresses_tmp` (
  `country` int(11) NOT NULL,
  `town_name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `town_quarter` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `district_name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `street` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `number` varchar(15) COLLATE utf8_czech_ci NOT NULL,
  `zip_code` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  UNIQUE KEY `FULLTEXT` (`town_name`,`town_quarter`,`district_name`,`street`,`number`,`zip_code`,`country`),
  KEY `country` (`country`),
  FULLTEXT KEY `town_name` (`town_name`),
  FULLTEXT KEY `town_quarter` (`town_quarter`),
  FULLTEXT KEY `district_name` (`district_name`),
  FULLTEXT KEY `street` (`street`),
  FULLTEXT KEY `number` (`number`),
  FULLTEXT KEY `zip_code` (`zip_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `value` text COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
