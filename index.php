<?php

$config = parse_ini_file('/etc/freenetis-addresses.ini') or die(json_encode(array()));

define(mysql_user, $config['mysql_user']);
define(mysql_password, $config['mysql_pass']);
define(mysql_server, $config['mysql_server']);
define(mysql_port, $config['mysql_port']);
define(mysql_db, $config['mysql_db']);

// do not modify
define(mysql_table, 'addresses');

set_time_limit(1);

// get searched address
$town = $_GET['town'];
$district = $_GET['district'];
$street = $_GET['street'];
$zip = $_GET['zip'];
$mode = $_GET['mode'];

// connect do database
$db = mysql_connect(mysql_server.":".mysql_port, mysql_user, mysql_password) or die(json_encode(array()));
mysql_query("SET CHARACTER SET utf8", $db) or die(json_encode(array()));
mysql_query("SET NAMES utf8", $db) or die(json_encode(array()));
mysql_select_db(mysql_db, $db) or die(json_encode(array()));

$addresses = array();

if ($mode == 'test')
{
	$addresses = array(
			'state' => TRUE,
			'message' => 'Server is running' 
		);
}
else
{
	if (!empty($street))
	{
		// find number
		$where = '';
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
		$query = "
			SELECT DISTINCT town_name, district_name, town_quarter, zip_code
			FROM ".mysql_table."
			WHERE town_name LIKE '".mysql_real_escape_string($town)."%'
			GROUP BY zip_code, district_name
			LIMIT 0,15";
	}

	// quit if no query
	if (!$query)
	{
		die(json_encode(array()));
	}
	
	// execute query
	$result = mysql_query($query) or die(json_encode(array(mysql_error())));

	// get results
	while ($row = mysql_fetch_assoc($result))
	{
		$addresses[] = $row;
	}
}

if ($mode == 'validate')
{
	if ($town && $street && $zip && $addresses)
	{
		$addresses = array(
			'state' => TRUE,
			'message' => 'Address is valid'
		);
	}
	else
	{
		$addresses = array(
			'state' => FALSE,
			'message' => 'Address is not valid' 
		);
	}
}

// send headers
@header('Cache-Control: no-cache, must-revalidate');
@header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
@header('Content-type: application/json; charset=utf-8');

// send results
echo json_encode($addresses);