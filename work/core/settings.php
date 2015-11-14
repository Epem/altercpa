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

// URLs
define ( 'BASEURL',		'http://work.cpa/' );
define ( 'SPACEURL',	'http://blog.cpa/' );
define ( 'LANDSURL',	'http://shop.cpa/' );

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

// Payment
define( 'WMR',			'R12345667890' ); // WebMoney Ruble Purse
define( 'WMK',			'' ); // WebMoney Auth Key

// Offers
define( 'OFFER_FILE',	PATH . 'data/offer/%d.jpg' );
define( 'OFFER_LOGO',	'/data/offer/%d.jpg' );

// ByteHand
define ( 'SMS_ID',		'' );	// ByteHand API ID
define ( 'SMS_KEY',		'' );	// ByteHand API Key
define ( 'SMS_SIGN',	'MyCPA' );
define ( 'SMS_LOGIN',	'' );	// ByteHand Login
define ( 'SMS_PASS',	'' );	// ByteHand Password
define ( 'SMS_COOKIE',	PATH . 'data/work/bytehand.txt' );

// Post Tracker
define ( 'SPSR_COOKIE',	PATH . 'data/work/spsr-%s.txt' );
define ( 'SPSR_CITY',	'Москва' );
define ( 'SPSR_CACHE',	PATH . 'data/work/spsr-cache-%s.txt' );
define ( 'SPSR_LOGIN',	'' );	// Default SPSR login
define ( 'SPSR_PASS',	'' );	// Default SPSR password
define ( 'SPSR_ID',		'' );	// Default SPSR ID
define ( 'RUP_API',		'' );	// RuPost API ID
define ( 'RUP_KEY',		'' );	// RuPost API Key
define ( 'RUP_WG',		'0.1' );
define ( 'RUP_FROM',	'101000' );	// RuPost API Index

// Address Parsing
define ( 'ADDR_XML',	'http://ahunter.ru/site/check?user=username;output=xml;query=' );	// AHunter XML check url
define ( 'ADDR_ALT',	'http://ahunter.ru/site/search?user=username;output=xml;query=' );	// AHunter XML search url
define ( 'ADDR_CACHE',	PATH . 'data/work/address-%s.txt' );

// Email settings
define( 'MAIL_FROM',	'noreply@work.cpa' );		// From address
define( 'MAIL_DOMAIN',	'work.cpa' );				// SMTP domain
define( 'MAIL_SERVER',	'ssl://smtp.yandex.ru' );	// SMTP server address
define( 'MAIL_PORT',	'465' );					// SMTP server port
define( 'MAIL_USER',	'noreply@work.cpa' );		// SMTP login
define( 'MAIL_PASS',	'password' );				// SMTP password

// News Images
define( 'DIR_NEWS',		PATH . 'data/news/' );
define( 'PATH_NEWS',	'/data/news/%s' );

// Support notification email
define( 'SUPPORT_NOTIFY',	'support@work.cpa' );

// Dates
date_default_timezone_set( 'Etc/GMT-3' );

//
// Cron-Related Cleanup Functions
//

function crontab ( $core ) {
	$core->cron->add ( 'control_cache', 'cron_control_cache', 86400 );
	$core->cron->add ( 'control_session', 'cron_control_session', 86400 );
	$core->cron->add ( 'control_cache', 'cron_control_work', 86400 );
}

function cron_control_cache ( $core ) {
	cron_control_cleanup ( DIR_CACHE, 86400 );
}

function cron_control_session ( $core ) {
	cron_control_cleanup ( DIR_SESSION, 2592000 );
}

function cron_control_work ( $core ) {
	cron_control_cleanup ( DIR_WORK, 864000 );
}

function cron_control_cleanup ( $dir, $timeout ) {

	$timeout = time() - $timeout;
	$d = @opendir ( $dir );
	while ( $f = @readdir ( $d ) ) {
		if ( is_file( $dir.$f ) && filemtime( $dir.$f ) < $timeout ) unlink( $dir.$f );
	} @closedir ( $d );

}

function msgo( $core, $msg ) {

	$ref = $core->server['HTTP_REFERER'];
	if ( strpos( $ref, 'message=' ) !== false ) {
		$core->go(preg_replace( '#message=([a-z0-9]+)#i', 'message='.$msg, $ref ));
	} else $core->go( $ref . ((strpos($ref,'?')!==false)?'&':'?') . 'message='.$msg );

}

function sms ( $from, $to, $text ) {
	$result = @file_get_contents( 'http://bytehand.com:3800/send?id='.SMS_ID.'&key='.SMS_KEY.'&to='.urlencode($to).'&from='.urlencode($from).'&text='.urlencode($text) );
    return ( $result === false ) ? false : true;
}

// end. =)