<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			landing zone / cron.php
 *  Description:	Lost clicks and orders processor
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

// Loading configuration
define( 'PATH', dirname(__FILE__) . '/' );
require_once PATH . 'config.php';

// Process the lost clicks
define( 'PATH', dirname(__FILE__) . '/' );
if (file_exists( PATH . 'click.txt' )) {	rename( PATH . 'click.txt', PATH . 'click-work.txt' );
	$clicks = file( PATH . 'click-work.txt' );
	$badclick = array();
	foreach ( $clicks as &$c ) if ( $req = trim( $c ) ) {		$res = @file_get_contents( BASEURL . 'click.php?' . $req );
		if (!( $res == 'ok' || $res == 'e' )) $badclick[] = $req;
	} unset ( $c, $clicks );
	if ( $badclick ) file_put_contents( PATH . 'click.txt', implode( "\r\n", $badclick ) . "\r\n", FILE_APPEND | LOCK_EX  );
	unlink( PATH . 'click-work.txt' );
}

// Process the lost orders
if (file_exists( PATH . 'query.txt' )) {
	rename( PATH . 'query.txt', PATH . 'query-work.txt' );
	$query = file( PATH . 'query-work.txt' );
	$badquery = array();
	foreach ( $query as &$qu ) if ( $post = trim( $qu ) ) {

		list( $skey, $request ) = unserialize( $post );
		$curl = curl_init( BASEURL . 'neworder?key=' . $skey );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $request );
		$result = curl_exec( $curl );
		curl_close( $curl );
		$r = explode ( ':', $result, 2 );
		if (!( $r[0] == 'ok' || $r[0] == 'e' )) $badquery[] = $post;

	} unset ( $qu, $query );
	if ( $badquery ) file_put_contents( PATH .  'query.txt', implode( "\r\n", $badquery ) . "\r\n", FILE_APPEND | LOCK_EX  );
	unlink( PATH . 'query-work.txt' );
}