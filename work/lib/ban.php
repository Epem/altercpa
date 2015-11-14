<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / ban.php
 *  Description:	Easy phone and IP bans
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

// Add IP-address to warn/ban list
function ban_ip ( $core, $ip, $raw = false ) {
	// Check the IP address
	if ( ! $raw ) $ip = ip2int( $ip );
	if ( ! $ip ) return false;

	// Add or update ban times info
	$bans = $core->db->field( "SELECT `times` FROM ".DB_BAN_IP." WHERE `ip` = '$ip' LIMIT 1" );
	if ( $bans ) {		if ( $bans > 9 ) { // Change status to banned			$core->db->query( "UPDATE ".DB_BAN_IP." SET `times` = `times` + 1, `status` = 1 WHERE `ip` = '$ip' LIMIT 1" );
		} else $core->db->query( "UPDATE ".DB_BAN_IP." SET `times` = `times` + 1 WHERE `ip` = '$ip' LIMIT 1" );
	} else $core->db->add( DB_BAN_IP, array( 'ip' => $ip, 'times' => 1 ) );

}

// Add phone number to warn/ban list
function ban_phone ( $core, $phone  ) {
	// Check the phone number
	$phone = preg_replace( '#([^0-9]+)#', '', $phone );
	if ( ! $phone ) return false;

	// Add or update ban times info
	$bans = $core->db->field( "SELECT `times` FROM ".DB_BAN_PH." WHERE `phone` = '$phone' LIMIT 1" );
	if ( $bans ) {
		if ( $bans > 9 ) { // Change status to banned
			$core->db->query( "UPDATE ".DB_BAN_PH." SET `times` = `times` + 1, `status` = 1 WHERE `phone` = '$phone' LIMIT 1" );
		} else $core->db->query( "UPDATE ".DB_BAN_PH." SET `times` = `times` + 1 WHERE `phone` = '$phone' LIMIT 1" );
	} else $core->db->add( DB_BAN_PH, array( 'phone' => $phone, 'times' => 1 ) );

}

// Check phones list for bans
function check_ip_bans( $core, $ips, $istext = false ) {

	if ( ! $ips ) return array();
	if ( $istext ) $ips = array_map( $ips, 'ip2int' );
	$ips = array_unique( $ips ); sort( $ips );

	if ( count( $ph ) ) {
		return $core->db->icol( "SELECT `ip`, `times` FROM ".DB_BAN_IP." WHERE `ip` IN ( ".implode( ", ", $ips )." )" );
	} else return array();

}

// Check phones list for bans
function check_phone_bans( $core, $phones ) {
	if ( ! $phones ) return array();
	$ph = array();
	foreach ( $phones as $p ) if ( $p = preg_replace( '#([^0-9]+)#', '', $p ) ) $ph[] = $p;
	$ph = array_unique( $ph ); sort( $ph );

	if ( count( $ph ) ) {		return $core->db->icol( "SELECT `phone`, `times` FROM ".DB_BAN_PH." WHERE `phone` IN ( '".implode( "', '", $ph )."' )" );
	} else return array();

}