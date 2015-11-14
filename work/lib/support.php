<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / support.php
 *  Description:	Support API library
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

//
// Add new message to the support list
// @type - 0 for user, 1 for admin
function support_add ( $core, $user, $type, $text ) {
	// Check all the parameters
	$user = (int) $user;
	$type = $type ? 1 : 0;
	$text = $core->text->line( $text );
	if (!( $core->user->id && $text && $user )) return false; // Bad infoming data

	$iptext = $core->server['REMOTE_ADDR'];
	$ip = ip2int( $iptext );

	$geoipdata = geoip( $core, $iptext );
	if ( $geoipdata ) {
		if ( $geoipdata['city'] ) {			$geoip = $geoipdata['city'];
		} elseif ( $geoipdata['region'] ) {
			$geoip = $geoipdata['region'];
		} elseif ( $geoipdata['district'] ) {
			$geoip = $geoipdata['district'];
		} elseif ( $geoipdata['country'] ) {
			$geoip = $geoipdata['country'];
		} else $geoip = '';
	} else $geoip = '';

	// Add new message to the list
	$sql = "INSERT INTO ".DB_SUPP." SET supp_user = '$user', user_id = '".$core->user->id."', user_name = '".$core->user->name."', supp_type = '$type', supp_time = '".time()."', supp_read = 0, supp_text = '$text', supp_ip = '$ip', supp_geo = '$geoip'";
	if ( $core->db->query( $sql ) && $id = $core->db->lastid() ) {
		// Count new messages in the list
		$cnt = $core->db->field( "SELECT COUNT(*) FROM ".DB_SUPP." WHERE supp_user = '$user' AND supp_type = '$type' AND supp_read = 0" );
		$data = array( 'supp_last' => time(), 'supp_user' => $core->user->id, 'supp_name' => $core->user->name, 'supp_type' => $type, 'supp_notify' => 0 );
		if ( $type ) {
			$data['supp_new'] = $cnt;
		} else $data['supp_admin'] = $cnt;
		$core->user->set( $user, $data );

		return $id;

	} else return false; // Database error

}
//

//
// Show messages in the list
// @type - 0 for user site window, 1 for admin control panel
// @from - numer of messages from which to start listing
function support_show ( $core, $user, $type, $from = 0 ) {
	// Check what to get from the base
	$user = (int) $user;
	$type = $type ? 1 : 0;
	$from = ( ( $from = (int) $from ) > 0 ) ? "< '$from'" : "> '".abs($from)."'";

	// Make an array of the messages
	$ms = array(); $ur = array( 0 => 0, 1 => 0 );
	$mms = $core->db->data( "SELECT * FROM ".DB_SUPP." WHERE supp_user = '$user' AND supp_id $from ORDER BY supp_id DESC LIMIT 10" );
	foreach ( $mms as &$m ) {
		// Add new item
		$ms[] = array(
			'id'		=> $m['supp_id'],
			'uid'		=> $m['user_id'],
			'user'		=> $m['user_name'],
			'link'		=> $type ? '/users/' . $m['user_id'] : '',
			'time'		=> smartdate( $m['supp_time'] ),
			'text'		=> $core->text->lines( $m['supp_text'] ),
			'uclass'	=> $m['supp_type'] ? 'user-alt' : 'user-blue',
			'rclass'	=> $m['supp_read'] ? '' : 'unread',
			'new'		=> $m['supp_read'] ? 0 : $m['supp_type'] != $type,
			'ip'		=> int2ip( $m['supp_ip'] ),
			'geo'		=> $m['supp_geo']
		);

		// Set unread parameters
		if ( $m['supp_read'] == 0 ) $ur[ $m['supp_type'] ? 0 : 1 ] = 1;

	} unset ( $m, $mms );

	// Mark messages as read and check activity
	if ( $ur[$type] ) {		$core->db->query( "UPDATE ".DB_SUPP." SET supp_read = 1 WHERE supp_user = '$user' AND supp_type = '".( $type ? 0 : 1 )."'" );
		if ( $type == 0  ) {			$core->user->set( $user, array( 'supp_new' => 0 ) );
		} else $core->user->set( $user, array( 'supp_admin' => 0 ) );
	}

	return $ms; // Message array completed

}
//

// lib-end. =)