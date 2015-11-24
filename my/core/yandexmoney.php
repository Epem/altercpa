<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / yandexmoney.php
 *  Description:	Yandex Money Payment
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
$id = (int) $core->post['label'];
if ( $id ) {
	$o = $core->db->row( "SELECT order_id, order_price, order_status, comp_id FROM ".DB_ORDER." WHERE order_id = '$id' LIMIT 1" );
	if ( ! $o['order_id'] ) die();
	if ( ((int) $core->post['withdraw_amount']) != ((int) $o['order_price']) ) die();
	$c = $core->wmsale->get( 'comp', $o['comp_id'] );
	if (!( $c['comp_id'] && $c['pay_ymr'] )) die();
} else die();

// Checking Hash
$hash = sha1( $core->post['notification_type'].'&'.$core->post['operation_id'].'&'.$core->post['amount'].'&'.$core->post['currency'].'&'.$core->post['datetime'].'&'.$core->post['sender'].'&'.$core->post['codepro'].'&'.$c['pay_ymk'].'&'.$core->post['label'] );
if ( $hash != $core->post['sha1_hash'] ) die ();

$type = ( $core->post['notification_type'] == 'p2p-incoming' ) ? 2 : 3;
$info = sprintf( $core->lang['comp_ymi'], $core->post['sender'], $core->post['amount'], $core->post['operation_id'] );
$edit = array( 'paid_ok' => $type, 'paid_time' => time(), 'paid_from' => $info );
if ( $r['order_status'] == 0 ) $edit['order_status'] = $edit['order_webstat'] = 1;
$core->db->edit( DB_ORDER, $edit, "order_id = '$id'" );
die ();