<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			spacing zone / cron.php
 *  Description:	Lost clicks processor
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
if (file_exists( PATH . 'click.txt' )) {	rename( PATH . 'click.txt', PATH . 'click-work.txt' );
	$clicks = file( PATH . 'click-work.txt' );
	$badclick = array();
	foreach ( $clicks as &$c ) if ( $req = trim( $c ) ) {		$res = @file_get_contents( BASEURL . 'click.php?' . $req );
		if (!( $res == 'ok' || $res == 'e' )) $badclick[] = $req;
	} unset ( $c, $clicks );
	if ( $badclick ) file_put_contents( dirname(__FILE__) . '/click.txt', implode( "\r\n", $badclick ) . "\r\n", FILE_APPEND | LOCK_EX  );
	unlink( PATH . 'click-work.txt' );
}