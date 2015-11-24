<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			index.php
 *  Description:	CPA order info
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
//error_reporting (E_ALL & ~E_NOTICE); // Hard debug
//error_reporting (E_ALL); // Light debug
error_reporting (0); // Production
define ( 'IN_ALTERCMS_CORE_ONE', true );
define ( 'ABSPATH', dirname(__FILE__).'/' );
define ( 'PATH', dirname(__FILE__).'/' );
include PATH . 'core/core.php';
header ( 'Content-Type: text/html; charset=utf-8' );

// Preparing Main Variables
$module = ( $core->get['m'] ) ? $core->get['m'] : null;
$action = ( $core->get['a'] ) ? $core->get['a'] : null;
$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );
$page	= ( $core->get['page'] > 0 ) ? (int) $core->get['page'] : 1;
$message = ( $core->get['message'] ) ? $core->get['message'] : null;

// Payment Processing
if ( $action == 'wm' ) require_once ( PATH . 'core/webmoney.php' );
if ( $action == 'ym' ) require_once ( PATH . 'core/yandexmoney.php' );

//
// Actions
//

switch ( $action ) {

  case 'find':
	if ( $id ) {		$core->go($core->url( 'm', $id ));
	} else $core->go($core->url( 'm', 'no' ));

  case 'pay-ok':
  	if ( $id = (int) $core->get['LMI_PAYMENT_NO'] ) {
		$core->go($core->url( 'mm', $id, 'ok' ));
	} else $core->go($core->url( 'm', 'no' ));

  case 'pay-fail':
  	if ( $id = (int) $core->get['LMI_PAYMENT_NO'] ) {
		$core->go($core->url( 'mm', $id, 'fail' ));
	} else $core->go($core->url( 'm', 'no' ));

}

//
// Modules
//

switch ( $module ) {
  case 'info':

	if ( ! $id ) $core->go($core->url( 'm', '' ));
	$order = $core->db->row( "SELECT * FROM ".DB_ORDER." WHERE order_id = '$id' LIMIT 1" );
	if ( ! $order['order_id'] ) $core->go($core->url( 'm', 'no' ));
	$offer = $core->wmsale->get( 'offer', $order['offer_id'] );

	$core->header();

	$core->tpl->load( 'body', 'order' );

	$core->tpl->vars( 'body', array(

		// Order
		'success'		=> $core->lang['order_success'],
		'order'			=> $core->lang['order'],
		'date'			=> $core->lang['date'],
		'status'		=> $core->lang['status'],
		'u_order'		=> $core->url( 'm', $order['order_id'] ),
		'order_id'		=> $order['order_id'],
		'order_time'	=> smartdate( $order['order_time'] ),
		'order_status'	=> $core->lang['statuso'][$order['order_webstat']],
		'order_price'	=> $order['order_price'],

		// Offer
		'offer'			=> $core->lang['offer_h'],
		'oname'			=> $core->lang['name'],
		'offer_name'	=> $offer['offer_descr'],

		// Delivery
		'delivery'		=> $core->lang['delivery'],
		'manual'		=> $core->lang['delivery_manual'],
		'track'			=> $core->lang['track'],
		'check'			=> $core->lang['check'],
		'type'			=> $core->lang['type'],
		'track_code'	=> $order['track_code'] ? $order['track_code'] : $core->lang['ona'],
		'track_type'	=> $core->lang['delivero'][$order['order_delivery']],
		'track_status'	=> $order['track_date'] ? sprintf( "%s: %s", $order['track_date'], $order['track_status'] ) : $core->lang['delivern'][$order['order_delivery']],
		'track_url'		=> sprintf( $core->lang['deliverc'][$order['order_delivery']], $order['track_code'] ),

	));

	if ( $order['order_delivery'] && ($order['order_webstat'] == 8 || $order['order_webstat'] == 9) ) $core->tpl->block( 'body', 'delivery' );
	if ( $core->get['message'] == 'ok' ) $core->tpl->block( 'body', 'payok' );
	elseif ( $core->get['message'] == 'fail' ) $core->tpl->block( 'body', 'payfail' );
	elseif ( strpos( $core->server['QUERY_STRING'], 'ok' ) != false ) $core->tpl->block( 'body', 'neworder' );

	if ( $offer['offer_payment'] && !$order['paid_ok'] && ( $order['order_status'] < 5 || $order['order_status'] == 6 ) ) {
		$comp = $core->wmsale->get( 'comp', $order['comp_id'] );
		$core->tpl->block( 'body', 'pay', array(
			'info'	=> $comp['pay_info'],
			'type'	=> $core->lang['pay_types'][$offer['offer_payment']],
		));

		if ( $comp['pay_wmr'] && $comp['pay_wmk'] ) $core->tpl->block( 'body', 'pay.wm', array( 'to' => $comp['pay_wmr'] ));
		if ( $comp['pay_ymr'] && $comp['pay_ymk'] ) $core->tpl->block( 'body', 'pay.ym', array( 'to' => $comp['pay_ymr'] ));

	}

	$core->tpl->output( 'body' );

	$core->footer();
  	break;

  case 'no':

	$core->header();

	$core->tpl->load( 'body', 'no' );

	$core->tpl->vars( 'body', array(
		'title'		=> $core->lang['notfound_h'],
		'text'		=> $core->lang['notfound_t'],
		'number'	=> $core->lang['search_n'],
		'find'		=> $core->lang['find'],
		'u_find'	=> $core->url( 'a', 'find', '' ),
	));

	$core->tpl->output( 'body' );

	$core->footer();

	break;

  default:

	$core->header();

	$core->tpl->load( 'body', 'index' );

	$core->tpl->vars( 'body', array(

		'title'		=> $core->lang['search_h'],
		'text1'		=> $core->lang['search_t1'],
		'text2'		=> $core->lang['search_t2'],
		'number'	=> $core->lang['search_n'],
		'find'		=> $core->lang['find'],
		'u_find'	=> $core->url( 'a', 'find', '' ),

	));

	$core->tpl->output( 'body' );

	$core->footer();

}

// end. =)