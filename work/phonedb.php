<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			phonedb.php
 *  Description:	Phone DB Cron Processing
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

// Loading Site Core
error_reporting (0);
define ( 'IN_ALTERCMS_CORE_ONE', true );
define ( 'ABSPATH', dirname(__FILE__).'/' );
define ( 'PATH', dirname(__FILE__).'/' );

include PATH . 'core/core.php';
set_time_limit( 0 );

$pagelists = file_get_contents( 'http://www.rossvyaz.ru/opendata/' );
if ( preg_match_all( '#href="(([\w\-\/]+)Kody([\w\-\_]+).csv)"#i', $pagelists, $pgl ) ) {	$pages = array_unique( $pgl[1] );
	foreach ( $pages as $p ) {
		if (substr( $p, 0, 2 ) == '//' ) $p = 'http:' . $p;
		if ( $p{0} == '/' ) $p = 'http://www.rossvyaz.ru' . $p;
		$page = file( $p );

		$oldcode = $st = $en = $scode = $ecode = 0;
		unset ( $page[0] );
        foreach ( $page as $pg ) {
			$pg = explode( ';', $pg );
			$pg = array_map( 'trim', $pg );
			$st = $pg[0].$pg[1];
			$scode = substr( $st, 0, 6 );

			if ( $scode > $oldcode ) {
				$en = $pg[0].$pg[2];
				$ecode = substr( $en, 0, 6 );
				$oper = iconv( 'windows-1251', 'utf-8', $pg[4] );
				$place = explode( '|', iconv( 'windows-1251', 'utf-8', $pg[5] ) );
				$pc = count( $place );

				if ( $pc > 1 ) {					if ( $pc == 2 ) {						$region = trim( $place[1] );
						$city = trim( $place[0] );
					} else {						$region = trim( $place[2] );
						$city = sprintf( "%s, %s", trim( $place[1] ), trim( $place[0] ) );
					}
				} else {					$city = '';
					$region = trim( $place[0] );
				}

				for ( $i = $scode; $i <= $ecode; $i++ ) {                 	$oldcode = $i;
                    $core->db->replace( DB_PDB, array(
                    	'phone'		=> $i,
                    	'operator'	=> $oper,
                    	'region'	=> $region,
                    	'city'		=> $city,
                    ));
				}

			}

        }

	}
} else die( 'No lists found' );