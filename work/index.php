<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			index.php
 *  Description:	Main CPA platform file
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
//error_reporting (E_ALL & ~E_NOTICE);
error_reporting (0);
define ( 'IN_ALTERCMS_CORE_ONE', true );
define ( 'ABSPATH', dirname(__FILE__).'/' );
define ( 'PATH', dirname(__FILE__).'/' );
include PATH . 'core/core.php';

// API functions
if ( $core->get['api'] ) {
	require_once PATH_LIB . 'api.php';
	api( $core, $core->get['api'], $core->get['f'], $core->get['t'], $core->get['id'] );
	$core->_die();
} elseif ( $core->server['REQUEST_URI'] == '/?wm' ) require_once ( PATH_LIB . 'webmoney.php' );

// Strict Host Working
if ( defined( 'STRICT_HTTPS' )&& $core->server['HTTPS'] != 'on' ) {
	$core->go( 'https://' . ( defined('STRICT_HOST') ? STRICT_HOST : $core->server['HTTP_HOST']  ). $core->server['REQUEST_URI'] );
} elseif ( defined( 'STRICT_HOST' )&& $core->server['HTTP_HOST'] != STRICT_HOST ) {
	$core->go( ( ($core->server['HTTPS'] == 'on') ? 'https' : 'http' ) . '://' . STRICT_HOST . $core->server['REQUEST_URI'] );
}

// Login and Register Page
if ( $core->user->auth == false ) {
	require_once ( PATH_LIB . 'register.php' );
	$core->_die();
} elseif ( $core->get['recoverpass'] ) $core->go( '/profile' );

if ( $core->user->ban ) {
	$core->tpl->load( 'body', 'ban' );
	$core->tpl->output( 'body' );
	$core->_die();
}

// Including working libraries
require_once ( PATH_LIB . 'base.php' );
if ( $core->user->work == -2 ) 	require_once ( PATH_LIB . 'referal.php' );
if ( $core->user->work == -1 ) 	require_once ( PATH_LIB . 'external.php' );
if ( $core->user->work == 0 || $core->user->work == 2 ) 	require_once ( PATH_LIB . 'webmaster.php' );
if ( $core->user->work >= 1 ) 	require_once ( PATH_LIB . 'orders.php' );
if ( $core->user->comp && $core->user->compad )	require_once ( PATH_LIB . 'company.php' );
if ( $core->user->level == 1 )	require_once ( PATH_LIB . 'admin.php' );

//
// Menu
//

$menu = array();
if ( $core->user->work == -2 )	$menu = referal_menu ( $core, $menu );
if ( $core->user->work == -1 )	$menu = external_menu ( $core, $menu );
if ( $core->user->work >= 1 )	$menu = order_menu ( $core, $menu );
if ( $core->user->work == 0 || $core->user->work == 2 )	$menu = webmaster_menu ( $core, $menu );
if ( $core->user->comp && $core->user->compad ) $menu = company_menu ( $core, $menu );
if ( $core->user->level == 1 )	$menu = admin_menu ( $core, $menu );
$menu = base_menu ( $core, $menu );

$newmenu = array ();
foreach ( $menu as $n => &$m )	{
	if ( is_array( $m ) ) {
		$sub = array();
		foreach ( $m as $mm ) $sub[] = array ( 'div' => $mm, 'link' => $core->url ( 'm', $mm ), 'name' => $core->lang['menu_sub_' . $mm] ? $core->lang['menu_sub_' . $mm] : $core->lang['menu_' . $mm] );
		$newmenu[] = array ( 'div' => $n, 'link' => $core->url ( 'm', $n ), 'name' => $core->lang['menu_' . $n], 'sub' => $sub );
	} else $newmenu[] = array ( 'div'	=> $m, 'link'	=> $core->url ( 'm', $m ), 'name'	=> $core->lang['menu_' . $m] );
}
$core->setmenu( $newmenu );
unset( $m, $menu, $newmenu );

//
// Actions
//

base_action ( $core );
if ( $core->user->level == 1 )	admin_action ( $core );
if ( $core->user->comp && $core->user->compad ) company_action ( $core );
if ( $core->user->work == -2 )	referal_action ( $core );
if ( $core->user->work >= 1 )	order_action ( $core );
if ( $core->user->work == 0 || $core->user->work == 2 )	webmaster_action ( $core );

//
// Modules
//

if ( $core->user->level == 1 )	admin_module ( $core );
base_module ( $core );
if ( $core->user->comp && $core->user->compad ) company_module ( $core );
if ( $core->user->work >= 1 )	order_module ( $core );
if ( $core->user->work == 0 || $core->user->work == 2 )	webmaster_module ( $core );
if ( $core->user->work == -1 )	external_module ( $core );
if ( $core->user->work == -2 )	referal_module ( $core );
base_404 ( $core );

$core->_die ();

// end. =)