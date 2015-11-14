<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			geoip.php
 *  Description:	GeoIP Cron Processing
 *  Author:			Anton 'AlterVision' Reznichenko - altervision13@gmail.com
 *

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

*******************************************************************************/

// Configs
error_reporting( 0 );
define ( 'IN_ALTERCMS_CORE_ONE', true );
define ( 'ABSPATH', dirname(__FILE__).'/' );
define ( 'PATH', dirname(__FILE__).'/' );
define( 'WORKPATH', PATH . 'data/work/' );
require_once PATH . 'core/config.php';

// Download the file
file_put_contents( WORKPATH . 'geo.zip', file_get_contents( 'http://ipgeobase.ru/files/db/Main/geo_files.zip' ) );
$mdf = md5_file( WORKPATH . 'geo.zip' );
if ( $mdf == file_get_contents( WORKPATH . 'geoip-md5.txt' ) ) {	unlink( WORKPATH . 'geo.zip' );
	die('uptodate');
}

// Open archive and extract DB files
$zip = new ZipArchive;
if ( $zip->open( WORKPATH . 'geo.zip' ) ) {	$zip->extractTo( WORKPATH );
	$zip->close();
	file_put_contents( WORKPATH . 'md5.txt', $mdf );
} else {	@unlink( WORKPATH . 'geo.zip' );
	die( 'nofile' );
}

// Connect to DB
$db = mysqli_connect( SQL_HOST, SQL_USER, SQL_PASS, SQL_BASE );
if ( ! $db ) die( 'db' );
mysqli_query( $db, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8', collation_connection='utf8_general_ci'" );

// Load IPs
$ip = fopen( WORKPATH . 'cidr_optim.txt', 'r' );
if ( $ip ) {	mysqli_query( $db, "TRUNCATE `".SQL_PREF."geowork`" );
	while ( $line = fgets( $ip ) ) {
		$ld = explode( "\t", trim($line) );
		$li = array( $ld[0], $ld[1], strtolower( $ld[3] ), (int) $ld[4] );
		$ll = "'" . implode( "', '", $li ) . "'";
		mysqli_query( $db, "INSERT INTO `".SQL_PREF."geowork` VALUES( $ll )" );
	} fclose( $ip );
	mysqli_query( $db, "RENAME TABLE `".SQL_PREF."geoip` TO `".SQL_PREF."geoip2`" );
	mysqli_query( $db, "RENAME TABLE `".SQL_PREF."geowork` TO `".SQL_PREF."geoip`" );
	mysqli_query( $db, "ALTER TABLE `".SQL_PREF."geoip` ORDER BY `ip` DESC" );
	mysqli_query( $db, "TRUNCATE `".SQL_PREF."geoip2`" );
	mysqli_query( $db, "RENAME TABLE `".SQL_PREF."geoip2` TO `".SQL_PREF."geowork`" );
}

// Load cities
$cf = fopen( WORKPATH . 'cities.txt', 'r' );
if ( $cf ) {
	mysqli_query( $db, "TRUNCATE `".SQL_PREF."geocity`" );
	while ( $line = fgets( $cf ) ) {		$line = iconv( 'windows-1251', 'utf-8', trim($line) );		$ld = explode( "\t", $line );
		$ll = "'" . implode( "', '", $ld ) . "'";
		mysqli_query( $db, "INSERT INTO `".SQL_PREF."geocity` VALUES( $ll )" );
	} fclose( $cf );
}

// Finished
mysqli_close( $db );
unlink( WORKPATH . 'geo.zip' );
unlink( WORKPATH . 'cidr_optim.txt' );
unlink( WORKPATH . 'cities.txt' );
