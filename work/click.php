<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			click.php
 *  Description:	Clicks processing utility
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
define ( 'PATH', dirname(__FILE__).'/' );
include PATH . 'core/config.php';

// Get click data
$flow	= (int) $_GET['f'];
$site	= (int) $_GET['s'];
$unique	= $_GET['u'] ? 1 : 0;
$space	= $_GET['p'] ? 1 : 0;
$date	= ( isset($_GET['tm']) && is_numeric($_GET['tm']) ) ? date( 'Ymd', $_GET['tm'] ) : date( 'Ymd' );
$target	= (int) $_GET['t'];
$utmi	= (int) $_GET['utmi'];
$utms	= (int) $_GET['utms'];
$utmc	= (int) $_GET['utmc'];

// Add clicks the simpliest way
if ( $flow && $site ) {
	$db = mysqli_connect( SQL_HOST, SQL_USER, SQL_PASS, SQL_BASE );
	if ( !$db ) die( 'e' );
	mysqli_query ( $db, "INSERT INTO `".SQL_PREF."click` SET `site_id` = '$site', `flow_id` = '$flow', `target_id` = '$target', `click_date` = '$date', `click_unique` = '$unique', `click_space` = '$space', utm_id = '$utmi', utm_src = '$utms', utm_cn = '$utmc'" );
	mysqli_close ( $db );
	die( 'ok' );

} else die( 'e' );

// end. =)