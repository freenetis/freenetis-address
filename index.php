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
 
function jstk2gps($x,$y,$h=200){
    /*Vypocet zemepisnych souradnic z rovinnych souradnic*/
	
	$a=6377397.15508; 
	$e=0.081696831215303;
	$n=0.97992470462083; 
	$konst_u_ro=12310230.12797036;
	$sinUQ=0.863499969506341; 
	$cosUQ=0.504348889819882;
	$sinVQ=0.420215144586493; 
	$cosVQ=0.907424504992097;
	$alfa=1.000597498371542; 
	$k=1.003419163966575;
	$ro=sqrt($x*$x+$y*$y);
	$epsilon=2*atan($y/($ro+$x));
	$D=$epsilon/$n; 
	$S=2*atan(exp(1/$n*log($konst_u_ro/$ro)))-M_PI_2;
	$sinS=sin($S);
	$cosS=cos($S);
	$sinU=$sinUQ*$sinS-$cosUQ*$cosS*cos($D);
	$cosU=sqrt(1-$sinU*$sinU);
	$sinDV=sin($D)*$cosS/$cosU; 
	$cosDV=sqrt(1-$sinDV*$sinDV);
	$sinV=$sinVQ*$cosDV-$cosVQ*$sinDV; 
	$cosV=$cosVQ*$cosDV+$sinVQ*$sinDV;
	$Ljtsk=2*atan($sinV/(1+$cosV))/$alfa;
	$t=exp(2/$alfa*log((1+$sinU)/$cosU/$k));
	$pom=($t-1)/($t+1);
	
	do
	{
		$sinB=$pom;
		$pom=$t*exp($e*log((1+$e*$sinB)/(1-$e*$sinB))); 
		$pom=($pom-1)/($pom+1);
	} 
	while (abs($pom-$sinB)>0.000000000000001);
	$Bjtsk=atan($pom/sqrt(1-$pom*$pom));
	
	/* Pravoúhlé souřadnice ve S-JTSK */   
	
	$a=6377397.15508; 
	$f_1=299.152812853;
	$e2=1-(1-1/$f_1)*(1-1/$f_1); 
	$ro=$a/sqrt(1-$e2*sin($Bjtsk)*sin($Bjtsk));
	$x=($ro+$H)*cos($Bjtsk)*cos($Ljtsk);  
	$y=($ro+$H)*cos($Bjtsk)*sin($Ljtsk);  
	$z=((1-$e2)*$ro+$H)*sin($Bjtsk);
		
	/* Pravoúhlé souřadnice v WGS-84*/
	
	$dx=570.69; 
	$dy=85.69; 
	$dz=462.84; 
	$wz=-5.2611/3600*M_PI/180;
	$wy=-1.58676/3600*M_PI/180;
	$wx=-4.99821/3600*M_PI/180; 
	$m=3.543*pow(10,-6); 
	$xn=$dx+(1+$m)*($x+$wz*$y-$wy*$z); 
	$yn=$dy+(1+$m)*(-$wz*$x+$y+$wx*$z); 
	$zn=$dz+(1+$m)*($wy*$x-$wx*$y+$z);
	
	/* Geodetické souřadnice v systému WGS-84*/
	
	$a=6378137.0; 
	$f_1=298.257223563;
	$a_b=$f_1/($f_1-1); 
	$p=sqrt($xn*$xn+$yn*$yn); 
	$e2=1-(1-1/$f_1)*(1-1/$f_1);
	$theta=atan($zn*$a_b/$p); 
	$st=sin($theta); 
	$ct=cos($theta);
	$t=($zn+$e2*$a_b*$a*$st*$st*$st)/($p-$e2*$a*$ct*$ct*$ct);
	$B=atan($t);  
	$L=2*atan($yn/($p+$xn));  
	$H=sqrt(1+$t*$t)*($p-$a/sqrt(1+(1-$e2)*$t*$t));
	
	/* Formát výstupních hodnot */   
	
	$B=$B/M_PI*180;				   
	//$sirka="N";
	if ($B<0)
	{
		$B=-$B; 
		//$sirka="S";
	}
	
	$stsirky=floor($B);  
	$B=($B-$stsirky)*60; 
	$minsirky=floor($B);
	$B=($B-$minsirky)*60; 
	$vtsirky=round($B*1000)/1000;
	//$sirka=$sirka+$stsirky+"°"+$minsirky+"'"+$vtsirky;
	$gps = $stsirky.'°'.$minsirky.'′'.$vtsirky.'″';
	
	$L=$L/M_PI*180;  
	//$delka="E";		 
	if ($L<0)
	{
		$L=-$L; 
		//$delka="W";
	}
	
	$stdelky=floor($L);  
	$L=($L-$stdelky)*60; 
	$mindelky=floor($L);
	$L=($L-$mindelky)*60; 
	$vtdelky=round($L*1000)/1000;
	
	$gps .= ' '.$stdelky.'°'.$mindelky.'′'.$vtdelky.'″';
		
	return $gps;
}

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
		
		if (empty($district))
		{
			$district = $town;
		}
		
		$match = array();
		if (preg_match('/((ev\.č\.)?[0-9][0-9]*(\/[0-9][0-9]*[a-zA-Z]*)*)$/m', $street, $match))
		{
			$street = preg_replace('/ ((ev\.č\.)?[0-9][0-9]*(\/[0-9][0-9]*[a-zA-Z]*)*)$/m', '', $street);
			
			// prepare query
			$query = "
				SELECT DISTINCT *
				FROM ".mysql_table."
				WHERE
					town_name LIKE '".mysql_real_escape_string($town)."' AND 
					zip_code LIKE '".mysql_real_escape_string($zip)."' AND
					street LIKE '".mysql_real_escape_string(trim($street))."' AND
					number LIKE '".mysql_real_escape_string($match[0])."' AND
					country = ".mysql_real_escape_string($country)." AND
					district_name LIKE '".mysql_real_escape_string($district)."'
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
	
	if ($street !== NULL)
	{
		// find number
		$select = 'town_name, street, district_name';
		$orderby = '';

		$match = array();
		if (preg_match('/((ev\.č\.)?[0-9][0-9]*(\/[0-9][0-9]*[a-zA-Z]*)*)$/m', $street, $match))
		{
			$street = preg_replace('/ ((ev\.č\.)?[0-9][0-9]*(\/[0-9][0-9]*[a-zA-Z]*)*)$/m', '', $street);
			$where = "AND number LIKE '".mysql_real_escape_string($match[0])."%'";
			$select = '*';
			$orderby = "ORDER BY IF (number LIKE '".mysql_real_escape_string($match[0])."', 0, 1), number";
		}
		else if (strrpos($street, ' ') !== FALSE && 
				 strrpos($street, ' ') + 1 == strlen($street))
		{
			$select = '*';
		}

		if (!empty($town))
		{
			$where = "$where AND town_name LIKE '".mysql_real_escape_string($town)."'";
		}

		if (!empty($zip))
		{
			$where = "$where AND zip_code LIKE '".mysql_real_escape_string($zip)."'";
		}

		if (!empty($district))
		{
			$where = "$where AND district_name LIKE '".mysql_real_escape_string($district)."'";
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
			$orderby
			LIMIT 0,15";
	}
	else if ($district !== NULL)
	{
		if (!empty($town))
		{
			$where = "$where AND town_name LIKE '".mysql_real_escape_string($town)."'";
		}
		
		if (!empty($country))
		{
			$where = "$where AND country = '".mysql_real_escape_string($country)."'";
		}

		$query = "
			SELECT DISTINCT district_name, town_name, town_quarter, IF ((district_name = town_name OR town_quarter LIKE '".mysql_real_escape_string($district)."') AND town_quarter NOT LIKE '', 1 , 0) AS same
			FROM ".mysql_table."
			WHERE (district_name LIKE '".mysql_real_escape_string($district)."%' OR town_quarter LIKE '".mysql_real_escape_string($district)."%')
			$where
			GROUP BY district_name, town_quarter
			ORDER BY same DESC, district_name ASC, town_quarter ASC
			LIMIT 0,15";
	}
	else if ($town !== NULL)
	{
		
		if (!empty($country))
		{
			$where = "AND country = '".mysql_real_escape_string($country)."'";
		}
		
		$query = "
			SELECT town_name, COUNT(district_name) as district_count
			FROM
			(
				SELECT town_name, district_name
				FROM ".mysql_table."
				WHERE town_name LIKE '".mysql_real_escape_string($town)."%'
				$where
				GROUP BY town_name, district_name
				ORDER BY town_name ASC
			) q
			GROUP BY town_name
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

// send results
if ($mode == 'gps')
{
	@header('Content-type: text/plain; charset=utf-8');
	if (count($result) == 1)
	{
		// Count GPS coordinates from J-STK
		echo jstk2gps($result[0]['jstk_x'], $result[0]['jstk_y']);
	}
}
else
{
	@header('Content-type: application/json; charset=utf-8');
	echo json_encode($result);
}