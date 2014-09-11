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

USE `addresses`; 

ALTER TABLE `addresses` ADD `jstk_y` VARCHAR( 15 ) NOT NULL ,
	ADD `jstk_x` VARCHAR( 15 ) NOT NULL;
	
ALTER TABLE `addresses_tmp` ADD `jstk_y` VARCHAR( 15 ) NOT NULL ,
	ADD `jstk_x` VARCHAR( 15 ) NOT NULL; 