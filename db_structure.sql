CREATE TABLE IF NOT EXISTS `addresses` (
  `town_name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `town_quarter` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `district_name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `street` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `number` varchar(15) COLLATE utf8_czech_ci NOT NULL,
  `zip_code` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  UNIQUE KEY `FULLTEXT` (`town_name`,`town_quarter`,`district_name`,`street`,`number`,`zip_code`),
  FULLTEXT KEY `town_name` (`town_name`),
  FULLTEXT KEY `town_quarter` (`town_quarter`),
  FULLTEXT KEY `district_name` (`district_name`),
  FULLTEXT KEY `street` (`street`),
  FULLTEXT KEY `number` (`number`),
  FULLTEXT KEY `zip_code` (`zip_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE IF NOT EXISTS `addresses_tmp` (
  `town_name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `town_quarter` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `district_name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `street` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `number` varchar(15) COLLATE utf8_czech_ci NOT NULL,
  `zip_code` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  UNIQUE KEY `FULLTEXT` (`town_name`,`town_quarter`,`district_name`,`street`,`number`,`zip_code`),
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
