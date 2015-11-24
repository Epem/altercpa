<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / core.php
 *  Description:	The CORE
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

// Unregister Globals
if ( isset($_REQUEST['GLOBALS']) ) die('GLOBALS overwrite attempt detected');
$noUnset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES', 'table_prefix');
$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
foreach ( $input as $k => $v ) {
	if ( !in_array($k, $noUnset) && isset($GLOBALS[$k]) ) {
	    $GLOBALS[$k] = NULL;
	    unset($GLOBALS[$k]);
	}
}

// Main Paths
define ('PATH_ROOT', 			ABSPATH);
define ('PATH_CORE',	   		ABSPATH . 'core/');
define ('PATH_LIB',	   			ABSPATH . 'lib/');

// Style Path
define ('PATH_TPL_MAIN',  		ABSPATH . 'style/%s.tpl');
define ('PATH_MTPL_MAIN',  		ABSPATH . 'style/mobile/%s.tpl');
define ('PATH_JS_MAIN',  		'style/%s.js');
define ('PATH_CSS_MAIN',  		'style/%s.css');
define ('PATH_LANG_MAIN',  		ABSPATH . 'core/lang_%s.php');

// Data Dirs
define ('DIR_CACHE',			ABSPATH . 'data/cache/');
define ('DIR_SESSION',			ABSPATH . 'data/session/');
define ('DIR_WORK',				ABSPATH . 'data/work/');

// Data Paths
define ('PATH_CACHE',			ABSPATH . 'data/cache/%s.txt');
define ('PATH_FILES',			ABSPATH . 'data/files/%s');
define ('PATH_DOCS',			ABSPATH . 'data/docs/');
define ('PATH_THUMB',			ABSPATH . 'data/thumb/%s');
define ('PATH_TPLS',			ABSPATH . 'data/tpls/%s.php');
define ('PATH_SESSION',			ABSPATH . 'data/session/%s.txt');
define ('PATH_STYLE',			ABSPATH . 'data/styles/%s/');
define ('PATH_STYLES',			ABSPATH . 'data/styles/' );
define ('FPDF_FONTPATH',		ABSPATH . 'data/font/' );

// API Function Files
require_once PATH_CORE . 'api.php';
require_once PATH_CORE . 'cache.php';
require_once PATH_CORE . 'cron.php';
require_once PATH_CORE . 'crypto.php';
require_once PATH_CORE . 'db.php';
require_once PATH_CORE . 'email.php';
require_once PATH_CORE . 'mainline.php';
require_once PATH_CORE . 'site.php';
require_once PATH_CORE . 'template.php';
require_once PATH_CORE . 'text.php';
require_once PATH_CORE . 'user.php';

// Including Configuration Files
require_once PATH_CORE . 'config.php';
require_once PATH_CORE . 'settings.php';

$configs = array (

	// Main Variables
	'get'		=> &$_GET,
    'post'		=> &$_POST,
    'files'		=> &$_FILES,
    'cookie'	=> &$_COOKIE,
    'session'	=> &$_SESSION,
    'server'	=> &$_SERVER,

    // Configs
    'lang'		=> 'ru',
    'crypto'	=> CRYPTO,

    // Database Settings
    'db'		=> array (
		'host'		=> SQL_HOST,
        'user'		=> SQL_USER,
        'pass'		=> SQL_PASS,
        'base'		=> SQL_BASE,
        'charset'	=> SQL_CHARSET,
        'collate'	=> SQL_COLLATE,
    ),

	// Template
	'tpl'		=> array (
		'basic'		=> PATH_TPL_MAIN,
		'cache'		=> PATH_TPLS,
	),

	// Mobile template
	'mtpl'		=> array (
		'basic'		=> PATH_MTPL_MAIN,
		'cache'		=> PATH_TPLS,
	),

	// Paths
	'cache'		=> PATH_CACHE,
	'media'		=> PATH_FILES,
	'thumb'		=> PATH_THUMB,
	'session'	=> PATH_SESSION,
	'js'		=> PATH_JS_MAIN,
	'css'		=> PATH_CSS_MAIN,
	'web_path'	=> '/',
	'webm_path'	=> '/m/',
	'oldurl'	=> false,

	// Language
	'lang_path'	=> PATH_LANG_MAIN,
	'lang_def'	=> 'ru',

);

if ( defined( 'MC_HOST' ) ) {
	$configs['mc'] = array(
    	'host'	=> MC_HOST,
    	'port'	=> MC_PORT,
    	'pref'	=> MC_PREF,
    	'exp'	=> 7200,
	);
}

$core = new Core ($configs);
if (! $core) die ('The Worst Error: Core was not created ...');

// Including main library files
require_once PATH_CORE . 'wmsale.php';
$core->wmsale = new WMsale( $core );

// Making Exit Function
function altercms_exit_procedure () {
	// Simply Unsetting $core variable, automatically calling the destructor object
	global $core; if (isset($core)) unset ($core);
}
register_shutdown_function ('altercms_exit_procedure');

header ("Content-Type: text/html; charset=utf8");

// end. =)