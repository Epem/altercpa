<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			spacing zone / config.php
 *  Description:	Spacing Zone Configuration
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

$id = (int) $_GET['id'];
if ( ! $id ) die( 'oops!' );

$data = json_decode( file_get_contents( BASEURL.'api/wm/land.json?oid=' . $id ), true );
if ( ! $data ) die( 'oops!!' );

$o = '<?
require_once "cms.php";
function ourl () {
static $theurl;
global $flow;
if ( $theurl ) return $theurl;
$defland = '.$data['default'].';
$lands = '.var_export( $data['lands'], true ).';
$space = '.var_export( $data['space'], true ).';
$theurl = geturl ( $lands, $space, $defland );
return $theurl;
}';

file_put_contents( PATH . 'offer'.$id.'.php', $o );
die( 'ok' );