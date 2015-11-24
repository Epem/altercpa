<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / webmoney.php
 *  Description:	WebMoney Payment Engine
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

// Checking Payment ID
$id = (int) $core->post['LMI_PAYMENT_NO'];
if ( $id ) {
	$o = $core->db->row( "SELECT order_id, order_price, order_status, comp_id FROM ".DB_ORDER." WHERE order_id = '$id' LIMIT 1" );
	if ( ! $o['order_id'] ) die();
	if ( ((int)$core->post['LMI_PAYMENT_AMOUNT']) != ((int)$o['order_price']) ) die();
	$c = $core->wmsale->get( 'comp', $o['comp_id'] );
	if (!( $c['pay_wmk'] && $c['pay_wmr'] )) die();
} else die();

// Checking Purse
if ( $core->post['LMI_PAYEE_PURSE'] != $c['pay_wmr'] ) die ();

// Checkign PreRequest
if ( ! $core->post['LMI_PREREQUEST'] ) {

	// Checking Hash
	$hash = $c['pay_wmr'] . $core->post['LMI_PAYMENT_AMOUNT'] . $core->post['LMI_PAYMENT_NO'] . $core->post['LMI_MODE'] . $core->post['LMI_SYS_INVS_NO'] . $core->post['LMI_SYS_TRANS_NO'] . $core->post['LMI_SYS_TRANS_DATE'] . $c['pay_wmk'] . $core->post['LMI_PAYER_PURSE'] . $core->post['LMI_PAYER_WM'];
	$hash = strtoupper(hash( 'sha256', $hash ));
	if ( $hash != $core->post['LMI_HASH'] ) die ();

	$info = sprintf( $core->lang['comp_wmi'], $core->post['LMI_PAYER_WM'], $core->post['LMI_PAYER_PURSE'], $core->post['LMI_SYS_INVS_NO'], $core->post['LMI_SYS_TRANS_NO'] );
	$edit = array( 'paid_ok' => 1, 'paid_time' => time(), 'paid_from' => $info );
	if ( $o['order_status'] == 0 ) $edit['order_status'] = $edit['order_webstat'] = 1;
	$core->db->edit( DB_ORDER, $edit, "order_id = '$id'" );

} else die( 'YES' ); // Answer for WM-PreRequest

header("HTTP/1.0 200 OK");
die ();