<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / track.php
 *  Description:	Russian Post Tracking
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

class PostTracker {
	private static function request ( $url, $post ) {
		$post['apikey'] = RUP_API;
		$reqmd5 = $post;
		$reqmd5[] = RUP_KEY;
		$post['hash'] = md5(implode( '|', $reqmd5 ));

		$curl = curl_init( $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $post );

		$result = curl_exec( $curl );
		curl_close( $curl );
		return $result ? json_decode( $result, true ) : false;

	}

	public static function check ( $code ) {
		$info = PostTracker::request( 'http://russianpostcalc.ru/api_v1.php', array( 'method' => 'parcel', 'rpo' => $code ) );
		if ( $info['status0'] ) {			$date = $info['date'];
			$status = sprintf( '%s - %s (%s, %s)', $info['status1'], $info['status0'], $info['index'], $info['place'] );
		} else $date = $status = false;
		return array( $date, $status );

	}

	public static function info ( $code ) {
		$info = PostTracker::request( 'http://russianpostcalc.ru/api_v1.php', array( 'method' => 'parcel', 'rpo' => $code ) );
		$data = array();

		if ( $info['status0'] ) {
			$log = json_decode( $info['log_json'], true );
			foreach ( $log as $l ) {             	$data[] = array(
             		'date' => $l['date'], 'city' => $l['place'], 'index' => $l['index'],
             		'pro' => $l['status1'], 'state' => $l['status0'],
             		'status' => sprintf( '%s - %s (%s)', $l['status1'], $l['status0'], $l['index'] ),
             	);
			}
		}

		return $data;

	}

}