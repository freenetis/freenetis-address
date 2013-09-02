<?php
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

$config = parse_ini_file('/etc/freenetis-addresses.ini') or die();

define("mysql_user", $config['mysql_user']);
define("mysql_password", $config['mysql_pass']);
define("mysql_server", $config['mysql_server']);
define("mysql_port", $config['mysql_port']);
define("mysql_db", $config['mysql_db']);

// do not modify
define("mysql_table", 'addresses');

set_time_limit(1);

// get searched address
$country = @$_GET['country'];
$town = @$_GET['town'];
$district = @$_GET['district'];
$street = @$_GET['street'];
$zip = @$_GET['zip'];

$mode = @$_GET['mode'];

// connect do database
$db = mysql_connect(mysql_server.":".mysql_port, mysql_user, mysql_password) or die();
mysql_query("SET CHARACTER SET utf8", $db) or die();
mysql_query("SET NAMES utf8", $db) or die();
mysql_select_db(mysql_db, $db) or die();

$result = array();

if ($mode == 'test')
{
	$result = array(
			'state' => TRUE,
			'message' => 'Server is running' 
		);
}
else if ($mode == 'validate')
{
	if (!empty($country) && !empty($town) && !empty($zip) && !empty($street))
	{
		// find number
		$where = '';

		$match = array();
		if (preg_match('((ev\.č\.)?[0-9][0-9]*(/[0-9][0-9]*[a-zA-Z]*)*)', $street, $match))
		{
			$street = preg_replace(' ((ev\.č\.)?[0-9][0-9]*(/[0-9][0-9]*[a-zA-Z]*)*)', '', $street);
			
			
			if (!empty($district))
			{
				$where = "AND district_name LIKE '".mysql_real_escape_string($district)."'";
			}
			else
			{
				$where = "AND district_name LIKE town_name";
			}

			// prepare query
			$query = "
				SELECT DISTINCT *
				FROM ".mysql_table."
				WHERE
					town_name LIKE '".mysql_real_escape_string($town)."' AND 
					zip_code LIKE '".mysql_real_escape_string($zip)."' AND
					street LIKE '".mysql_real_escape_string(trim($street))."' AND
					number LIKE '".mysql_real_escape_string($match[0])."' AND
					country = ".mysql_real_escape_string($country)."
					$where
				LIMIT 0,15";
			
			$mysql_result = mysql_query($query) or die(json_encode(array(mysql_error())));
			
			if (mysql_num_rows($mysql_result))
			{
				$result = array(
					'state' => TRUE,
					'message' => 'Address is valid'
				);
			}
			else
			{
				$result = array(
					'state' => FALSE,
					'message' => 'Address is not valid' 
				);
			}
		}
		else
		{
			$result = array(
				'state' => FALSE,
				'message' => 'Address is not valid' 
			);
		}
	}
	else
	{
		$result = array(
			'state' => FALSE,
			'message' => 'Address is not valid' 
		);
	}
}
else
{
	$where = '';
	
	if (!empty($street))
	{
		// find number
		$select = 'town_name, street, district_name';

		$match = array();
		if (preg_match('((ev\.č\.)?[0-9][0-9]*(/[0-9][0-9]*[a-zA-Z]*)*)', $street, $match))
		{
			$street = preg_replace(' ((ev\.č\.)?[0-9][0-9]*(/[0-9][0-9]*[a-zA-Z]*)*)', '', $street);
			$where = "AND number LIKE '%".mysql_real_escape_string($match[0])."%'";
			$select = '*';
		}
		else if (strrpos($street, ' ') !== FALSE && 
				 strrpos($street, ' ') + 1 == strlen($street))
		{
			$select = '*';
		}

		if (!empty($town))
		{
			$where = "$where AND town_name LIKE '".mysql_real_escape_string($town)."%'";
		}

		if (!empty($zip))
		{
			$where = "$where AND zip_code LIKE '".mysql_real_escape_string($zip)."%'";
		}

		if (!empty($district))
		{
			$where = "$where AND district_name LIKE '".mysql_real_escape_string($district)."%'";
		}
		
		if (!empty($country))
		{
			$where = "$where AND country = '".mysql_real_escape_string($country)."'";
		}

		// prepare query
		$query = "
			SELECT DISTINCT $select
			FROM ".mysql_table."
			WHERE street LIKE '".mysql_real_escape_string(trim($street))."%'
			$where
			LIMIT 0,15";
	}
	else if (!empty ($district))
	{
		if (!empty($town))
		{
			$where = "$where AND town_name LIKE '".mysql_real_escape_string($town)."%'";
		}
		
		if (!empty($country))
		{
			$where = "$where AND country = '".mysql_real_escape_string($country)."'";
		}

		$query = "
			SELECT DISTINCT district_name, town_name, town_quarter, zip_code
			FROM ".mysql_table."
			WHERE district_name LIKE '".mysql_real_escape_string($district)."%'
			$where
			GROUP BY zip_code
			LIMIT 0,15";
	}
	else if (!empty($town))
	{
		
		if (!empty($country))
		{
			$where = "AND country = '".mysql_real_escape_string($country)."'";
		}
		
		$query = "
			SELECT DISTINCT town_name, district_name, town_quarter, zip_code,
				IF (town_name LIKE district_name, 1, 0) AS same
			FROM ".mysql_table."
			WHERE town_name LIKE '".mysql_real_escape_string($town)."%'
				AND zip_code NOT LIKE ''
			$where
			GROUP BY zip_code, district_name
			ORDER BY same DESC, district_name ASC
			LIMIT 0,15";
	}

	// quit if no query
	if (!$query)
	{
		die(json_encode(array()));
	}
	
	// execute query
	$mysql_result = mysql_query($query) or die(json_encode(array(mysql_error())));

	// get results
	while ($row = mysql_fetch_assoc($mysql_result))
	{
		$result[] = $row;
	}
}

// send headers
@header('Cache-Control: no-cache, must-revalidate');
@header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
@header('Content-type: application/json; charset=utf-8');

// send results
echo json_encode($result);
