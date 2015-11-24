<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / settings.php
 *  Description:	Site additional configs
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

// Database Tables
define( 'DB_BAN_IP',	SQL_PREF . 'ban_ip' );
define( 'DB_BAN_PH',	SQL_PREF . 'ban_phone' );
define( 'DB_BL',		SQL_PREF . 'bl' );
define( 'DB_CASH',		SQL_PREF . 'cash' );
define( 'DB_CLICK',		SQL_PREF . 'click' );
define( 'DB_COMP',		SQL_PREF . 'comp' );
define( 'DB_DOMAIN',	SQL_PREF . 'domain' );
define( 'DB_EXT',		SQL_PREF . 'ext' );
define( 'DB_FLOW',		SQL_PREF . 'flow' );
define( 'DB_GEOIP',		SQL_PREF . 'geoip' );
define( 'DB_GEOCITY',	SQL_PREF . 'geocity' );
define( 'DB_NEWS',		SQL_PREF . 'news' );
define( 'DB_OFFER',		SQL_PREF . 'offer' );
define( 'DB_ORDER',		SQL_PREF . 'order' );
define( 'DB_PDB',		SQL_PREF . 'pdb' );
define( 'DB_PHONE',		SQL_PREF . 'pdb' );
define( 'DB_SITE',		SQL_PREF . 'site' );
define( 'DB_STATS',		SQL_PREF . 'stats' );
define( 'DB_STORE',		SQL_PREF . 'store' );
define( 'DB_SUPP',		SQL_PREF . 'supp' );
define( 'DB_TARGET',	SQL_PREF . 'target' );
define( 'DB_USER',		SQL_PREF . 'user' );
define( 'DB_VARS',		SQL_PREF . 'vars' );

//
// Cron-Related Cleanup Functions
//

function crontab ( $core ) {	$core->cron->add ( 'control_cache', 'cron_control_cache', 86400 );
	$core->cron->add ( 'control_session', 'cron_control_session', 86400 );
}

function cron_control_cache ( $core ) {
	cron_control_cleanup ( DIR_CACHE, 86400 );
}

function cron_control_session ( $core ) {
	cron_control_cleanup ( DIR_SESSION, 2592000 );
}

function cron_control_cleanup ( $dir, $timeout ) {

	$timeout = time() - $timeout;
	$d = @opendir ( $dir );
	while ( $f = @readdir ( $d ) ) {
		if (is_file( $dir.$f )) {
			if ( filemtime( $dir.$f ) < $timeout ) unlink( $dir.$f );
		}
	}
	@closedir ( $d );

}

function msgo( $core, $msg ) {

	$ref = $core->server['HTTP_REFERER'];
	if ( strpos( $ref, 'message=' ) !== false ) {
		$core->go(preg_replace( '#message=([a-z0-9]+)#i', 'message='.$msg, $ref ));
	} else $core->go( $ref . ((strpos($ref,'?')!==false)?'&':'?') . 'message='.$msg );

}

// end. =)