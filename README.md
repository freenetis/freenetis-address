FreenetIS CZ addresses database
=================

Requirements
------------
 - unzip
 - PHP 5.2.0
 - MySQL 5.1


Installation
------------
 - Create new database
 - Create database structure using db_structure.sql script
 - Run install.sh under root user
 - Run /var/www/freenetis-addresses/import.sh under root user for first time database update

Upgrade to 1.1.0
----------------
 - Before upgrading, execute SQL script in [upgrade_1.1.0.sql](upgrade_1.1.0.sql)
 
Changelog
---------
Changelog in debian format is available [here](changelog).
