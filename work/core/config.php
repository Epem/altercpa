<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / config.php
 *  Description:	Database configuration
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

if ( !defined('IN_ALTERCMS_CORE_ONE') ) die("Hacking attempt");

//
// Database Configuration
//

define ( 'SQL_HOST', 'localhost' );			// Database server host
define ( 'SQL_BASE', 'work_cpa' );			// Database name
define ( 'SQL_USER', 'root' );				// Database user
define ( 'SQL_PASS', '' );					// Database password
define ( 'SQL_PREF', 'cpa_' );				// Database prefix
define ( 'SQL_CHARSET', 'utf8' );			// Main charset
define ( 'SQL_COLLATE', 'utf8_general_ci');	// Collation charset

// Additional configs
define ( 'CRYPTO', 'iuyBG6rvDC5E4cs^v5rTfN8u9mk*uK98&TgB&5rD*');	// Random string
//define ( 'STRICT_HOST',	'work.cpa' );	// Use only this hostname
//define ( 'STRICT_HTTPS', 	true );			// Use only HTTPS connection

// end. =)