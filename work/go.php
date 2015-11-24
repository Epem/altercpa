<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			go.php
 *  Description:	Redirection block
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

// Getting the ID
$id = (int) $_GET['flid'];
if ( ! $id ) die( 'oups' );

// Loading Site Core
error_reporting (0);
define ( 'IN_ALTERCMS_CORE_ONE', true );
define ( 'PATH', dirname(__FILE__).'/' );
include PATH . 'core/cache.php';
include PATH . 'core/config.php';
include PATH . 'core/db.php';
include PATH . 'core/settings.php';
include PATH . 'lib/wmsale.php';

// Create working objects
$db = new sql_db ( SQL_HOST, SQL_USER, SQL_PASS, SQL_BASE, SQL_CHARSET, SQL_COLLATE );
if ( defined( 'MC_HOST' ) ) {
	$cache = new CacheControl ( PATH . 'cache/%s.txt' );
} else $cache = new CacheControl ( PATH . 'cache/%s.txt', array( 'host' => MC_HOST, 'port' => MC_PORT, 'pref' => MC_PREF, 'exp' => 7200 ) );

// Make new WMsale instance
$core = new stdClass();
$core->db = $db;
$core->cache = $cache;
$wmsale = new WMsale ( $core );

// Get the flow data
$flow = $wmsale->get ( 'flow', $id );
if ( ! $flow['flow_id'] ) die( 'oups' );

// Make the URL based on the offer countries data
if ( $flow['flow_url'] ) {

	$offer = $wmsale->get( 'offer', $flow['offer_id'] );
	$cntr = $offer['offer_country'] ? $offer['offer_country'] : 'ru';
	$cntr = explode( ',', $cntr );

	$ipl = array( $_SERVER['REMOTE_ADDR'] );
	if ( $_SERVER['HTTP_CLIENT_IP'] ) $ipl[] = $_SERVER['HTTP_CLIENT_IP'];
	if ( $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ) {
		$xffd = explode( '.', $xff );
		if (!(
			$xffd[0] == 10 ||
			( $xffd[0] == 172 && $xffd[1] > 15 && $xffd[1] < 33 ) ||
			( $xffd[0] == 192 && $xffd[0] == 168 ) ||
			( $xffd[0] == 169 && $xffd[0] == 254 )
		)) $ipl[] = $xff;
	}
	if ( $ff = $_SERVER['HTTP_FORWARDED'] ) {
		$xff = trim( str_replace( 'for=', '', $ff ), '"' );
		if ( strpos( $xff, ':' ) !== false ) {
			$xffa = explode( ':', $xff );
			$xff = $xffa[0];
		}
		$xffd = explode( '.', $xff );
		if (!(
			$xffd[0] == 10 ||
			( $xffd[0] == 172 && $xffd[1] > 15 && $xffd[1] < 33 ) ||
			( $xffd[0] == 192 && $xffd[0] == 168 ) ||
			( $xffd[0] == 169 && $xffd[0] == 254 )
		)) $ipl[] = $xff;
	}
	$ipl = array_unique( $ipl );

	$match = false; $iplog = array();
	foreach ( $ipl as $ip ) {
		$ipi = sprintf( "%u", ip2long( $ip ) );
		$ipd = $db->field( "SELECT `country` FROM `".DB_GEOIP."` WHERE `ip` < '$ipi' ORDER BY `ip` DESC LIMIT 1" );
		$iplog[] = "$ip ( $ipd )";
		if ( in_array( $ipd, $cntr ) ) $match = true;
	}

	$url = $match ? false : $flow['flow_url'];

} else $url = false;

// Make the URL based on the flow data
if ( ! $url ) {

	if ( $flow['flow_space'] ) {
		$url = $wmsale->get( 'site', $flow['flow_space'], 'site_url' );
		$fid = $id . '-' . $flow['flow_site'];
	} elseif ( $flow['flow_site'] ) {
		$url = $wmsale->get( 'site', $flow['flow_site'], 'site_url' );
		$fid = $id;
	} else die( 'oups' );

	$url = 'http://' . $url . '/?' . ( $flow['flow_param'] ? 'flow=' : '' ) . $fid . ( $flow['flow_cb'] ? '&cb' : '' );

}

// Get parameters
if ( count( $_GET ) > 1 ) {
	unset ( $_GET['flid'] );
	$getline = http_build_query( $_GET );
	$url .= ( strpos( $url, '?' ) ? '&' : '?' ) . http_build_query( $_GET );
}

// Write to log
$f = fopen ( PATH . 'log.txt', 'a' );
fprintf( $f, "%s\t%s\t%s\r\n", implode( ' ', $iplog ), $url, json_encode( $_SERVER ) );
fclose( $f );

// Redirect to specific location
header( 'Location: ' . $url );

// end. =)