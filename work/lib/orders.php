<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / orders.php
 *  Description:	Order processing
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

// Load common library
require_once PATH_LIB . 'common.php';

//
// Orders control
//

// Take order
function order_take ( $core, $id = 0 ) {
	$id = (int) $id;
	if ( ! $id ) {
		if ( ! $core->user->comp ) return false;    	$id = $core->db->field( "SELECT order_id FROM ".DB_ORDER." WHERE user_id = '".$core->user->id."' AND order_status = 2 LIMIT 1" );
    	if ( $id ) return $id;
    	$id = $core->db->field( "SELECT order_id FROM ".DB_ORDER." WHERE user_id = '".$core->user->id."' AND order_status IN ( 3, 4 ) AND order_recall < '".time()."' LIMIT 1" );
    	if ( $id ) return $id;
    	$id = $core->db->field( "SELECT order_id FROM ".DB_ORDER." WHERE user_id = 0 AND comp_id = '".$core->user->comp."' LIMIT 1" );
    	if ( ! $id ) return false;
	} else {     	$uid = $core->db->field( "SELECT user_id FROM ".DB_ORDER." WHERE order_id = '$id' LIMIT 1" );
     	if ( $uid ) return false;
	}

	order_edit( $core, $id, array( 'user' => $core->user->id, 'status' => 2 ) );
	return $id;

}


// Accept processing
function order_accept ( $action ) {
	if ( $action == 'ok' ) return array( 'accept' => 1, 'calls' => 1 );
	if ( $action == 'shave' ) return array( 'accept' => 1, 'calls' => 1, 'shave' => 1 );
	if ( substr( $action, 0, 2 ) == 're' ) return array( 'status' => 3, 'calls' => 1, 'rec' => time() + ( 60 * (int) substr( $action, 2 ) ) );
	if ( substr( $action, 0, 2 ) == 'no' ) return array( 'status' => 4, 'calls' => 1, 'rec' => time() + ( 60 * (int) substr( $action, 2 ) ) );
	if ( substr( $action, 0, 6 ) == 'cancel' ) return array( 'status' => 5, 'calls' => 1, 'reason' => (int) substr( $action, 6 ) );
	return false;

}

// Courier Listings
function order_courier ( $core ) {
	$core->mainline->add( $core->lang['orders_h'], $core->url( 'm', 'order' ) );
	$core->mainline->add( $core->lang['courier_h'] );
	$core->header ();

	$title	= $core->lang['courier_h'];
	$action	= $core->url ( 'a', 'order-courier', '' );
	$method	= 'post';
	$field 	= array(
		array( 'type' => 'line', 'value' => $core->text->lines( $core->lang['courier_t'] ) ),
		array( 'type' => 'date', 'name' => 'from', 'head' => $core->lang['courier_from'], 'descr' => $core->lang['courier_from_d'] ),
		array( 'type' => 'date', 'name' => 'to', 'head' => $core->lang['courier_to'], 'descr' => $core->lang['courier_to_d'] ),
		array( 'type' => 'checkbox', 'name' => 'new', 'head' => $core->lang['courier_new'], 'descr' => $core->lang['courier_new_d'], 'checked' => 1 ),
		array( 'type' => 'checkbox', 'name' => 'mark', 'head' => $core->lang['courier_mark'], 'descr' => $core->lang['courier_mark_d'], 'checked' => 1 ),
		array( 'type' => 'checkbox', 'name' => 'done', 'head' => $core->lang['courier_done'], 'descr' => $core->lang['courier_done_d'], 'checked' => 0 ),
	);
	$button = array(array('type' => 'submit', 'value' => $core->lang['download']));
	$core->form ('comp', $action, $method, $title, $field, $button);

	$core->footer ();
	$core->_die();

}

// Delivery processing
function order_delivery( $core ) {

	$core->mainline->add( $core->lang['orders_h'], $core->url( 'm', 'order' ) );
	$core->mainline->add( $core->lang['delivery_monitor'], $core->url( 'm', 'delivery' ) );
	$core->header();

	// Switch mode
	if ( $md = $core->get['md'] ? 1 : 0 ) {
		// Only call mode
		$f = 9;
		$tt = time() - 10000;
		$where = array( 'order_status = 9', "track_call < '$tt'", 'track_result < 2' );

	} else {
		// Status filtering
		if ( isset( $core->get['f'] ) && $core->get['f'] != '' ) {
			$f = (int) $core->get['f'];
		} else $f = false;
		if ( $f == 8 || $f == 9 ) {
			$where = array( "order_status = '$f'" );
		} else $where = array( 'order_status IN ( 8, 9 )' );

	}

	// User filter
	if ( $core->user->level || $core->user->call ) {
		if ( isset( $core->get['c'] ) && $core->get['c'] ) {
			$c = (int) $core->get['c'];
			$where[] = "comp_id = '$c'";
		} else $c = false;
	} else $where[] = "comp_id = '".$core->user->comp."'";

	// Search
	if ( isset( $core->get['s'] ) && $core->get['s'] ) {
		$s = $core->text->line( $core->get['s'] );
		if ( preg_match( '#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#i', $s ) && $ips = ip2int( $s ) ) {
           	$where[] = " order_ip = '$ips' ";
		} elseif ( preg_match( '#^[0-9]{11}$#i', $s ) ) {
           	$where[] = " order_phone = '$s' ";
		} else {
			require_once PATH_CORE . 'search.php';
			$search = new SearchWords( $core->get['s'] );
			if ( $s = $search->get() ) {
				$where[] = $search->field(array( 'order_name', 'order_addr', 'order_street', 'order_city', 'order_area' ));
			} else $s = false;
		}
	} else $s = false;

	// Type filtering
	if ( isset( $core->get['t'] ) && $core->get['t'] != '' ) {
		$t = (int) $core->get['t'];
		$where[] = "order_delivery = '$t'";
	} else $t = false;

	// Offer filtering
	if ( isset( $core->get['o'] ) && $core->get['o'] ) {
		$o = (int) $core->get['o'];
		$where[] = "offer_id = '$o'";
	} else $o = false;

	// WebMaster filtering
	if ( isset( $core->get['wm'] ) && $core->get['wm'] ) {
		$wm = (int) $core->get['wm'];
		$where[] = "wm_id = '$wm'";
	} else $wm = false;

	// Date filtering
	if ( $d = $core->get['d'] ) {
		$dd = explode( '-', $d );
		$ds = mktime( 0, 0, 0, $dd[1], $dd[2], $dd[0] );
		$de = mktime( 23, 59, 59, $dd[1], $dd[2], $dd[0] );
		$where[] = "( order_time BETWEEN '$ds' AND '$de' )";
	} else $d = false;

	$where = implode( ' AND ', $where );

	$page = ( $core->get['page'] > 0 ) ? (int) $core->get['page'] : 1;
	$sh = 20; $st = $sh * ( $page - 1 );
	$orders = $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE $where" );
	$order = $orders ? $core->db->data( "SELECT * FROM ".DB_ORDER." WHERE $where ORDER BY track_result ASC, order_time DESC LIMIT $st, $sh" ) : false;
	$offer = $core->wmsale->get( 'offers' );
	$callscheme = ( $callscheme = $core->wmsale->get( 'comp', $core->user->comp, 'callscheme' ) ) ? $callscheme : 'tel:+%s';

	$core->tpl->load( 'body', 'delivery' );

    $core->tpl->vars ('body', array (
		'offer'			=> $core->lang['offer'],
		'phone'			=> $core->lang['phone'],
		'name'			=> $core->lang['username'],
		'address'		=> $core->lang['address'],
		'time'			=> $core->lang['time'],
		'price'			=> $core->lang['price'],
		'status'		=> $core->lang['status'],
		'action'		=> $core->lang['action'],
		'pay'			=> $core->lang['pay'],
		'edit'			=> $core->lang['edit'],
		'del'			=> $core->lang['del'],
		'confirm'		=> $core->lang['confirma'],
		'track_confirm'	=> $core->lang['track_confirm'],
		'info'			=> $core->lang['inf'],
		'company'		=> $core->lang['company'],
		'd'				=> $d,
		'md'			=> $md,
		's'				=> $search ? $search->get() : $s,
		'f8'			=> ( $f == 8 ) ? 'selected="selected"' : '',
		'f9'			=> ( $f == 9 ) ? 'selected="selected"' : '',
		'pages'			=> pages ( $core->url( 'm', 'delivery?' ) . ( $wm ? 'wm='.$wm.'&' : '' ) . ( $t ? 't='.$t.'&' : '' ) . ( $d ? 'd='.$d.'&' : '' ) . ( $s ? 's='.$s.'&' : '' ) . ( $c ? 'c='.$c.'&' : '' ) . ( $f ? 'f='.$f.'&' : '' ) . ( $md ? 'md=1&' : '' ) . ( $o ? 'o='.$o : '' ), $orders, $sh, $page ),
		'shown'			=> sprintf( $core->lang['shown'], $st+1, min( $st+$sh, $orders ), $orders ),
		'filter'		=> $core->lang['filter'],
		'date'			=> $core->lang['date'],
		'search'		=> $core->lang['search'],
		'find'			=> $core->lang['find'],
		'u_trackinfo'	=> $core->url( 'a', 'track-info', 0 ),
		'mode'			=> $core->lang['trackmode'.$md],
		'c_mode'		=> $md ? 'deliver' : 'phone',
		'u_mode'		=> $core->url( 'm', 'delivery?' ) . ( $t ? 't='.$t.'&' : '' ) . ( $d ? 'd='.$d.'&' : '' ) . ( $s ? 's='.$s.'&' : '' ) . ( $c ? 'c='.$c.'&' : '' ) . ( $md ? '' : 'md=1&' ) . ( $o ? 'o='.$o : '' )
    ));

	foreach ( $core->lang['delivery'] as $i => $st ) {
		$core->tpl->block( 'body', 'type', array(
			'name'		=> $st,
			'value'		=> $i,
			'select'	=> ( $t !== null && $t == $i ) ? 'selected="selected"' : '',
		));
	}

	if ( $core->user->level || $core->user->call ) {
		$comp = $core->wmsale->get( 'comps' );
		$core->tpl->block( 'body', 'comps' );
		foreach ( $comp as $ci => $cn ) {
			$core->tpl->block( 'body', 'comps.c', array(
				'name'		=> $cn,
				'value'		=> $ci,
				'select'	=> ( $c == $ci ) ? 'selected="selected"' : '',
			));
		}
	}

	foreach ( $offer as $i => $of ) {
		$core->tpl->block( 'body', 'offer', array(
			'name'		=> $of,
			'value'		=> $i,
			'select'	=> ( $o == $i ) ? 'selected="selected"' : '',
		));
	}

	$td = strtotime(date('d.m.Y'));
	if ( $order ) foreach ( $order as &$r ) {

		$addr = $r['order_addr'];
		if ( $r['order_street'] ) $addr = $r['order_street'] . ', ' . $addr;
		if ( $r['order_city'] ) $addr = $r['order_city'] . ', ' . $addr;
		if ( $r['order_area'] ) $addr = $r['order_area'] . ', ' . $addr;
		if ( $r['order_index'] ) $addr = $r['order_index'] . ', ' . $addr;
		$addr = trim( $addr, ', ' );
		$user = $r['wm_id'] ? $core->user->get( $r['wm_id'] ) : array();

		$od = strtotime( $r['track_date'] );
		$dd = round( ($td - $od) / 86400 );

		$rowclass = '';
		$md = ( date('H') < 12 ) ? 2 : 1;
		if ( $dd < 4 ) $rowclass = 'drc-g1';
		if ( $dd < $md ) $rowclass = 'drc-g2';
		if ( $dd > 7 ) $rowclass = 'drc-r1';
		if ( $dd > 15 ) $rowclass = 'drc-r2';

		$calltime = time() - $r['track_call'];
		$cls = 'green';
		if ( $calltime > 86400 ) $cls = 'yellow';
		if ( $calltime > 259200 ) $cls = 'red';
		if ( $calltime > 604800 ) $cls = 'red fat';

		$core->tpl->block( 'body', 'ord', array(
			'id'			=> $r['order_id'],
			'rowclass'		=> $rowclass,
			'offer'			=> $offer[$r['offer_id']],
			'name'			=> $search ? $search->highlight( $r['order_name'] ) : $r['order_name'] ,
			'addr'			=> $search ? $search->highlight( $addr ) : $addr,
			'phone'			=> $search ? $search->highlight( $r['order_phone'] ) : $r['order_phone'],
			'phone_call'	=> sprintf( $callscheme, $r['order_phone'] ),
			'phone_ok'		=> $r['order_phone_ok'] ? 'ok' : 'bad',
			'count'			=> $r['order_count'],
			'price'			=> rur( $r['order_price'] ),
			'time'			=> smartdate( $r['order_time'] ),
			'stid'			=> $r['order_status'],
			'status'		=> $core->lang['statuso'][$r['order_status']],
			'edit'			=> $core->url ( 'i', 'order', $r['order_id'] ),
			'result'		=> (int) $r['track_result'],
			'call'			=> $r['track_result'] ? $core->lang['trackcalls'][$r['track_result']] : $core->lang['tracknocall'],
			'calls'			=> $r['track_calls'] ? '(' . $r['track_calls'] . ')' : '',
			'called'		=> $r['track_call'] ? smartdate( $r['track_call'] ) : '',
			'cls'			=> $cls,
			'delivery'		=> $r['order_delivery'],
			'delivern'		=> $core->lang['delivers'][$r['order_delivery']],
			'track_cls'		=> $r['track_status'] ? ( ($r['order_status'] == 9) ? 'green' : 'blue' ) : 'red',
			'track_check'	=> sprintf( $core->lang['track_check'], smartdate( $r['track_check'] ) ),
			'track_info'	=> ( $r['track_status'] ) ? sprintf( "%s: %s", $r['track_date'], $r['track_status'] ) : $core->lang['track_wait'],
			'track_url'		=> sprintf( $core->lang['deliveru'][$r['order_delivery']], $r['track_code'] ),
			'track_code'	=> $r['track_code'],
			'u_deliver'		=> $core->url( 'a', 'order-arrive', $r['order_id'] ),
			'u_confirm'		=> $core->url( 'a', 'order-done', $r['order_id'] ),
			'u_return'		=> $core->url( 'a', 'order-return', $r['order_id'] ),
			'u_call'		=> $core->url( 'a', 'order-trackcall', $r['order_id'] ),
			'uid'			=> $r['wm_id'],
			'uname'			=> $r['wm_id'] ? ( ( $core->user->level || $core->user->call || $r['ext_id'] ) ? $user['user_name'] : $r['wm_id'] ) : $core->lang['order_src_sh'],
			'uclass'		=> $r['order_check'] ? 'warn' : ( $r['wm_id'] ? ( $r['ext_id'] ? 'ext' : ( ( ($core->user->level || $core->user->call) && $user['user_vip'])  ? 'vip' : 'user' ) ) : 'search' ),
		));

		if ( $r['order_status'] == 8 ) $core->tpl->block( 'body', 'ord.confirm' );
		foreach ( $core->lang['trackcalls'] as $v => $n ) $core->tpl->block( 'body', 'ord.action', array( 'n' => $n, 'v' => $v ) );

	} unset ( $r, $order );

	$core->tpl->output( 'body' );

	$core->footer();
	$core->_die();

}

// Security controls
function order_security( $core ) {

	$core->mainline->add( $core->lang['orders_h'], $core->url( 'm', 'order' ) );
	$core->mainline->add( $core->lang['security_control'], $core->url( 'm', 'delivery' ) );
	$core->header();

	$where = array( 'order_status BETWEEN 6 AND 9', 'order_check = 1' );

	// User filter
	if ( $core->user->level || $core->user->call ) {
		if ( isset( $core->get['c'] ) && $core->get['c'] ) {
			$c = (int) $core->get['c'];
			$where[] = "comp_id = '$c'";
		} else $c = false;
	} else $where[] = "comp_id = '".$core->user->comp."'";

	// Search
	if ( isset( $core->get['s'] ) && $core->get['s'] ) {
		$s = $core->text->line( $core->get['s'] );
		if ( preg_match( '#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#i', $s ) && $ips = ip2int( $s ) ) {
           	$where[] = " order_ip = '$ips' ";
		} elseif ( preg_match( '#^[0-9]{11}$#i', $s ) ) {
           	$where[] = " order_phone = '$s' ";
		} else {
			require_once PATH_CORE . 'search.php';
			$search = new SearchWords( $core->get['s'] );
			if ( $s = $search->get() ) {
				$where[] = $search->field(array( 'order_name', 'order_addr', 'order_street', 'order_city', 'order_area' ));
			} else $s = false;
		}
	} else $s = false;

	// Offer filtering
	if ( isset( $core->get['o'] ) && $core->get['o'] ) {
		$o = (int) $core->get['o'];
		$where[] = "offer_id = '$o'";
	} else $o = false;

	// WebMaster filtering
	if ( isset( $core->get['wm'] ) && $core->get['wm'] ) {
		$wm = (int) $core->get['wm'];
		$where[] = "wm_id = '$wm'";
	} else $wm = false;

	// Date filtering
	if ( $d = $core->get['d'] ) {
		$dd = explode( '-', $d );
		$ds = mktime( 0, 0, 0, $dd[1], $dd[2], $dd[0] );
		$de = mktime( 23, 59, 59, $dd[1], $dd[2], $dd[0] );
		$where[] = "( order_time BETWEEN '$ds' AND '$de' )";
	} else $d = false;

	$where = implode( ' AND ', $where );

	$page = ( $core->get['page'] > 0 ) ? (int) $core->get['page'] : 1;
	$sh = 20; $st = $sh * ( $page - 1 );
	$orders = $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE $where" );
	$order = $orders ? $core->db->data( "SELECT * FROM ".DB_ORDER." WHERE $where ORDER BY order_time DESC LIMIT $st, $sh" ) : false;
	$offer = $core->wmsale->get( 'offers' );
	$callscheme = ( $callscheme = $core->wmsale->get( 'comp', $core->user->comp, 'callscheme' ) ) ? $callscheme : 'tel:+%s';

	$core->tpl->load( 'body', 'security' );

    $core->tpl->vars ('body', array (
    	'text'			=> $core->text->lines( $core->lang['security_control_t'] ),
		'offer'			=> $core->lang['offer'],
		'phone'			=> $core->lang['phone'],
		'name'			=> $core->lang['username'],
		'address'		=> $core->lang['address'],
		'time'			=> $core->lang['time'],
		'status'		=> $core->lang['status'],
		'company'		=> $core->lang['company'],
		'd'				=> $d,
		's'				=> $search ? $search->get() : $s,
		'pages'			=> pages ( $core->url( 'm', 'security?' ) . ( $wm ? 'wm='.$wm.'&' : '' ) . ( $d ? 'd='.$d.'&' : '' ) . ( $s ? 's='.$s.'&' : '' ) . ( $c ? 'c='.$c.'&' : '' ) . ( $o ? 'o='.$o : '' ), $orders, $sh, $page ),
		'shown'			=> sprintf( $core->lang['shown'], $st+1, min( $st+$sh, $orders ), $orders ),
		'filter'		=> $core->lang['filter'],
		'date'			=> $core->lang['date'],
		'search'		=> $core->lang['search'],
		'find'			=> $core->lang['find'],
		'u_trackinfo'	=> $core->url( 'a', 'track-info', 0 ),
    ));

	if ( $core->user->level || $core->user->call ) {
		$comp = $core->wmsale->get( 'comps' );
		$core->tpl->block( 'body', 'comps' );
		foreach ( $comp as $ci => $cn ) {
			$core->tpl->block( 'body', 'comps.c', array(
				'name'		=> $cn,
				'value'		=> $ci,
				'select'	=> ( $c == $ci ) ? 'selected="selected"' : '',
			));
		}
	}

	foreach ( $offer as $i => $of ) {
		$core->tpl->block( 'body', 'offer', array(
			'name'		=> $of,
			'value'		=> $i,
			'select'	=> ( $o == $i ) ? 'selected="selected"' : '',
		));
	}

	if ( $order ) foreach ( $order as &$r ) {

		$addr = $r['order_addr'];
		if ( $r['order_street'] ) $addr = $r['order_street'] . ', ' . $addr;
		if ( $r['order_city'] ) $addr = $r['order_city'] . ', ' . $addr;
		if ( $r['order_area'] ) $addr = $r['order_area'] . ', ' . $addr;
		if ( $r['order_index'] ) $addr = $r['order_index'] . ', ' . $addr;
		$addr = trim( $addr, ', ' );
		$user = $r['wm_id'] ? $core->user->get( $r['wm_id'] ) : array();

		$core->tpl->block( 'body', 'ord', array(
			'id'			=> $r['order_id'],
			'rowclass'		=> $rowclass,
			'offer'			=> $offer[$r['offer_id']],
			'name'			=> $search ? $search->highlight( $r['order_name'] ) : $r['order_name'] ,
			'addr'			=> $search ? $search->highlight( $addr ) : $addr,
			'phone'			=> $search ? $search->highlight( $r['order_phone'] ) : $r['order_phone'],
			'phone_call'	=> sprintf( $callscheme, $r['order_phone'] ),
			'phone_ok'		=> $r['order_phone_ok'] ? 'ok' : 'bad',
			'count'			=> $r['order_count'],
			'price'			=> rur( $r['order_price'] ),
			'time'			=> smartdate( $r['order_time'] ),
			'stid'			=> $r['order_status'],
			'status'		=> $core->lang['statuso'][$r['order_status']],
			'edit'			=> $core->url ( 'i', 'order', $r['order_id'] ),
			'delivery'		=> $r['order_delivery'],
			'delivern'		=> $core->lang['delivers'][$r['order_delivery']],
			'track_cls'		=> $r['track_status'] ? ( ($r['order_status'] == 9) ? 'green' : 'blue' ) : 'red',
			'track_check'	=> sprintf( $core->lang['track_check'], smartdate( $r['track_check'] ) ),
			'track_info'	=> ( $r['track_status'] ) ? sprintf( "%s: %s", $r['track_date'], $r['track_status'] ) : $core->lang['track_wait'],
			'track_url'		=> sprintf( $core->lang['deliveru'][$r['order_delivery']], $r['track_code'] ),
			'u_uncheck'		=> $core->url( 'a', 'order-uncheck', $r['order_id'] ),
			'u_reset'		=> $core->url( 'a', 'order-reset', $r['order_id'] ),
			'src'			=> $r['flow_id'] ? $r['flow_id'] : ( $r['ext_id'] ? $r['ext_src'] : 0 ),
			'uid'			=> $r['wm_id'],
			'uname'			=> $r['wm_id'] ? $user['user_name'] : $core->lang['order_src_sh'],
			'uclass'		=> $r['wm_id'] ? ( $r['ext_id'] ? 'ext' : ( $user['user_vip']  ? 'vip' : 'user' ) ) : 'search',
		));

	} else $core->tpl->block( 'body', 'nord' );
	unset ( $r, $order );

	$core->tpl->output( 'body' );

	$core->footer();
	$core->_die();

}

// Notification processing
function order_footer ( $core ) {
	$core->tpl->load( 'notify', 'notify' );
	$core->tpl->vars( 'notify', array(
		'url'	=> $core->url( 'a', 'order-notify', 0 ) . '?prev=',
		'ourl'	=> $core->url( 'm', 'orders' ),
		'prev'	=> time(),
		'text'	=> $core->lang['order_notify']
	));
	$core->tpl->output( 'notify' );

}

//
// Public processing functions
//

// Menu
function order_menu ( $core, $menu ) {
	if ( $core->user->work == 1 ) {		$core->enque_css( 'notifIt' );
		$core->enque_js( 'jquery' );
		$core->enque_js( 'notifIt' );
		$core->handle( 'footer', 'order_footer' );
	}

	$menu['order'] = array( 'order', 'delivery', 'security' );
	return $menu;

}

// Actions processing
function order_action ( $core ) {
	$action = ( $core->get['a'] ) ? $core->get['a'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );

	switch ( $action ) {

	  case 'order-notify':

		$prev = (int) $core->get['prev'];
		if ( $core->user->comp && (!$core->user->call) ) {
			echo json_encode(array(
				'previous'	=> time(),
				'ords'		=> $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE order_status = 1 AND order_time >= '$prev' AND comp_id = '".$core->user->comp."'" ),
			));
		} else {			echo json_encode(array(
				'previous'	=> time(),
				'ords'		=> $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE order_status = 1 AND order_time >= '$prev'" ),
			));
		}

	  	$core->_die();

	  case 'order-spsr':

		$comp 	= $core->user->comp ? $core->wmsale->get( 'comp', $core->user->comp ) : false;
		$to		= $core->text->line( $core->post['to'] );
		$area 	= $core->text->line( $core->post['area'] );
		$price 	= $core->text->line( $core->post['price'] );

		require_once PATH . 'lib/spsr.php';
		if ( $comp['comp_spsr_login'] && $comp['comp_spsr_pass'] ) {
			$spsr = new SPSRtrack ( $comp['comp_spsr_login'], $comp['comp_spsr_pass'], $comp['comp_spsr'], SPSR_COOKIE );
		} else $spsr = new SPSRtrack ( SPSR_LOGIN, SPSR_PASS, SPSR_ID, SPSR_COOKIE );
		$info = $spsr->price ( $comp['comp_spsr_from'] ? $comp['comp_spsr_from'] : SPSR_CITY, $to, $area, $price );
		unset ( $spsr );

		echo json_encode( $info );
		$core->_die();

	  case 'order-rupost':

		$to		= (int) $core->get['to'];
		$price 	= (int) $core->get['price'];

		$req = $reqmd5 = array( 'apikey' => RUP_API, 'method' => 'calc', 'from_index' => RUP_FROM, 'to_index' => $to, 'weight' => RUP_WG, 'ob_cennost_rub' => $price );
		$reqmd5[] = RUP_KEY;
		$req['hash'] = md5(implode( '|', $reqmd5 ));
		$info = json_decode( curl( 'http://russianpostcalc.ru/api_v1.php', $req ), true );
		if ( $info['calc'] ) {
			$d = 0; $c = 0;
			foreach ( $info['calc'] as $i ) {
		    	if ( $i['type'] == 'rp_1class' ) {
					$d = $i['days'];
					$c = $i['cost'];
		         	break;
		    	}
			}
			$res = $d ? array( 'ok' => 1, 'dd' => $d, 'cost' => $c ) : array( 'error' => 'nodelivery' );
		} else $res = array( 'error' => 'bad' );

		echo json_encode( $res );
	  	$core->_die();

	  case 'order-phone':

		$phone = preg_replace( '#([^0-9]+)#', '', $core->get['phone'] );
		$ptc = substr( $phone, 1, 6 );
		$data = $core->db->row( "SELECT * FROM ".DB_PDB." WHERE `phone` = '$ptc' LIMIT 1" );
		if ( $data ) {
			$place = $data['region'];
			if ( $data['city'] ) $place .= ', ' . $data['city'];			printf( "<b>База</b>: %s (%s)", $data['operator'], $place  );
		}

		$curl = curl_init( 'http://mnp.tele2.ru/gateway.php?'.substr( $phone, 1 ) );
		curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0' );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $curl, CURLOPT_REFERER, 'http://mnp.tele2.ru/whois.html' );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
			'Accept: application/json, text/javascript, */*; q=0.01',
			'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3',
			'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
			'X-Requested-With: XMLHttpRequest',
		));
		$tele2 = curl_exec( $curl );
		curl_close( $curl );

		if ( $tele2 ) {         	$tele2info = json_decode( $tele2, true );
         	if ( is_array( $tele2info['response'] ) ) {         		if ( $data ) echo '<br />';				printf( "<b>Tele2</b>: %s (%s)", $tele2info['response']['mnc']['value'], $tele2info['response']['geocode']['value'] );
         	}
		}
	  	$core->_die();

	  case 'order-move':
	  	$comp = (int) $core->post['comp'];
		if ( ( $core->user->level || $core->user->call ) && $comp && order_edit( $core, $id, array( 'comp' => $comp ) )) {
			msgo( $core, 'move' );
		} else msgo( $core, 'nomove' );

	  case 'order-pickup':
		if ( $oid = order_take( $core, $id ) ) {			$core->go($core->url( 'i', 'order', $oid ));
		} else $core->go($core->url( 'm', 'order', 'pickup' ));

	  case 'order-call':
		$status = $core->text->link( $core->post['status'] );
		if ( $status == 'del' && ! $core->user->level ) msgo( $core, 'call' );
	  	if ( $status == 'shave' && ! ( $core->user->level || $core->user->shave ) )msgo( $core, 'call' );
		if ( $cs = order_accept( $status ) ) order_edit( $core, $id, $cs );
		msgo( $core, 'call' );

	  case 'order-send':
		$code = $core->text->line( $core->post['code'] );
		if (order_edit( $core, $id, array( 'status' => 8, 'track' => $code ) )) {
			msgo( $core, 'send' );
		} else msgo( $core, 'nocode' );

	  case 'order-trackcall':
	  	$status = (int) $core->post['status'];
		$core->db->query( "UPDATE ".DB_ORDER." SET track_calls = track_calls + 1, track_result = '$status', track_call = '".time()."' WHERE order_id = '$id' LIMIT 1" );
		msgo( $core, 'called' );

	  case 'order-esend':
		if (order_edit( $core, $id, array( 'status' => 8 ) )) {
			msgo( $core, 'send' );
		} else msgo( $core, 'nocode' );

	  case 'order-snew':
		$core->db->query( "UPDATE ".DB_ORDER." SET order_courier = 0 WHERE order_id = '$id' LIMIT 1" );
		msgo( $core, 'save' );

	  case 'order-sold':
		$core->db->query( "UPDATE ".DB_ORDER." SET order_courier = 1 WHERE order_id = '$id' LIMIT 1" );
		msgo( $core, 'save' );

	  case 'order-courier':

		$from = $core->post['from'] ? form2date( $core->post['from'] ) : false;
		$to = $core->post['to'] ? form2date( $core->post['to'] ) : false;
		$onew = $core->post['new'] ? 1 : 0;
		$mark = $core->post['mark'] ? 1 : 0;
		$done = $core->post['done'] ? 1 : 0;

		$comp = $core->wmsale->get( 'comp', $core->user->comp );
		require_once PATH_LIB . 'addr.php';
		require_once PATH_LIB . 'docs.php';
		docs_spsr_make( $core, $comp, $from, $to, $onew, $mark, $done );
		$core->_die();

	  case 'order-packed':
		if (order_edit( $core, $id, array( 'status' => 7 ) )) {
			msgo( $core, 'pack' );
		} else msgo( $core, 'error' );

	  case 'order-arrive':
		if (order_edit( $core, $id, array( 'status' => 9 ) )) {
			msgo( $core, 'arrive' );
		} else msgo( $core, 'error' );

	  case 'order-done':
		if (order_edit( $core, $id, array( 'status' => 10 ) )) {
			msgo( $core, 'done' );
		} else msgo( $core, 'error' );

	  case 'order-return':
		if (order_edit( $core, $id, array( 'status' => 11 ) )) {
			msgo( $core, 'done' );
		} else msgo( $core, 'error' );

	  case 'order-uncheck':
		if (order_edit( $core, $id, array( 'check' => 0 ) )) {
			msgo( $core, 'done' );
		} else msgo( $core, 'error' );

	  case 'order-reset':
		if (order_edit( $core, $id, array( 'status' => 12 ) )) {
			msgo( $core, 'done' );
		} else msgo( $core, 'error' );

	  case 'order-docs':

        $ord = $core->db->row ( "SELECT * FROM ".DB_ORDER." WHERE order_id = '$id' LIMIT 1" );
        if ( $core->user->level || $core->user->call || $core->user->id == $ord['user_id'] || $core->user->comp == $ord['comp_id'] ) {			$comp = $core->wmsale->get( 'comp', $ord['comp_id'] );
			require_once PATH_LIB . 'docs.php';
			docs_xls_make( $ord, $comp );
			$core->_die();
        } else $core->go($core->url( 'mm', '', 'access' ));

	  case 'order-edit':

		$changes = array();
		$order = $core->db->row( "SELECT * FROM ".DB_ORDER." WHERE order_id = '$id' LIMIT 1" );
		$status = $order['order_status'];

		// Basic order info
		if (isset( $core->post['name'] ))	$changes['name']	= $core->text->line( $core->post['name'] );
		if (isset( $core->post['addr'] ))	$changes['addr']	= $core->text->line( $core->post['addr'] );
		if (isset( $core->post['area'] ))	$changes['area']	= $core->text->line( $core->post['area'] );
		if (isset( $core->post['city'] ))	$changes['city']	= $core->text->line( $core->post['city'] );
		if (isset( $core->post['street'] ))	$changes['street']	= $core->text->line( $core->post['street'] );
		if (isset( $core->post['phone'] ))	$changes['phone']	= preg_replace( '#([^0-9]+)#', '', $core->post['phone'] );
		if (isset( $core->post['index'] ))	$changes['index']	= (int) $core->post['index'];
		if (isset( $core->post['track'] ))	$changes['track']	= $core->text->line( $core->post['track'] );

		// Item delivery and counts
		if (isset( $core->post['delivery'] )) $changes['delivery'] = (int) $core->post['delivery'];
		if (isset( $core->post['discount'] )) $changes['discount'] = (int) $core->post['discount'];
		if (isset( $core->post['more'] )) 	  $changes['more'] = 	 (int) $core->post['more'];
		if (isset( $core->post['counts'] )) {			$changes['counts'] = array();
			foreach ( $core->post['counts'] as $i => $c ) if ( $c = (int) $c ) $changes['counts'][ (int) $i ] = $c;
		}
		if (isset( $core->post['comment'] )) $changes['comment'] = $core->text->line( $core->post['comment'] );

		if ( isset( $core->post['meta'] ) && is_array( $core->post['meta'] ) ) {			$changes['meta'] = array();
			foreach ( $core->post['meta'] as $k => $v ) $changes['meta'][$k] = stripslashes( $v );
		}

		// Check for status
		$act = $core->text->link( $core->post['act'] );
		switch ( $status ) {
		  case 2: case 3: case 4: // Order accept progress
		  	if ( $status == 'del' && ! $core->user->level ) break;
		  	if ( $status == 'shave' && ! ( $core->user->level || $core->user->shave ) ) break;
			if ( $cs = order_accept( $act ) ) $changes += $cs;
		  	break;

		  case 6: // Packing
			if ( $act == 'done' )		$changes['status'] = 7;
		  	break;

		  case 7: // Sending
			if ( $act == 'done' )		$changes['status'] = 8;
			if ( $act == 'back' )		$changes['status'] = 6;
		  	break;

		  case 8: case 9: // Delivery and payment
			if ( $act == 'done' )		$changes['status'] = $status + 1;
			if ( $act == 'return' )		$changes['status'] = 11;
			if ( $act == 'back' )		$changes['status'] = $status - 1;
		  	break;

		}

		// Checks and controls of orders
		if ( $core->post['check'] ) $changes['check'] = 1;
		if ( $core->post['uncheck'] ) $changes['check'] = 0;

		// Saving order data
		order_edit ( $core, $id, $changes, $order );

		// Processing bans
		if ( $core->post['banip'] || $core->post['banphone'] ) {			require_once PATH . 'lib/ban.php';
			if ( $core->post['banip'] ) ban_ip( $core, $order['order_ip'], true );
			if ( $core->post['banphone'] ) ban_phone( $core, $order['order_phone'] );
		}

		// Processing order cancels
		if ( $core->post['delip'] || $core->post['delphone'] ) {			$sql = "SELECT order_id FROM ".DB_ORDER." WHERE order_id != '".$order['order_id']."' AND order_status < 5 AND comp_id = '".$order['comp_id']."'";
			if ( $core->post['delip'] ) $sql .= " AND order_ip = '".$order['order_ip']."'";
			if ( $core->post['delphone'] ) $sql .= " AND order_phone = '".$order['order_phone']."'";
			$ids = $core->db->col( $sql );
			foreach ( $ids as $i ) order_edit( $core, $i, array( 'status' => 5, 'reason' => 7 ) );
		}

		// Order save competed, returning back
		if ( $core->post['next'] ) {
			$core->go($core->url( 'a', 'order-pickup', '' ));
		} else $core->go( $core->post['r'] ? $core->post['r'] : $core->url( 'mm', 'order', 'save' ) );

	  case 'track-info':

		$order = $core->db->row( "SELECT * FROM ".DB_ORDER." WHERE order_id = '$id' LIMIT 1" );
		if ( $order['track_code'] ) {

			$core->tpl->load( 'track', 'track' );
			$core->tpl->vars( 'track', array( 'id' => $id ) );

			switch ( $order['order_delivery'] ) {
			  case 1:
           		require_once PATH . 'lib/track.php';
           		$info = PostTracker::info( $order['track_code'] );
				break;

			  case 2:
           		require_once PATH . 'lib/spsr.php';
           		$info = SPSRtrack::info( $order['track_code'] );
				break;

			}

			foreach ( $info as $i ) {
				$core->tpl->block( 'track', 'place', array(
                   	'date'		=> $i['date'] . ( $i['time'] ? ' ' . $i['time'] : '' ),
                   	'status'	=> $i['status'],
                   	'city'		=> $i['city'],
				));
			}

			$core->tpl->output( 'track' );

		}
	  	$core->_die();

	}

	return false;

}

// Module processing
function order_module ( $core ) {
	$module	= ( $core->get['m'] ) ? $core->get['m'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );
	$page	= ( $core->get['page'] > 0 ) ? (int) $core->get['page'] : 1;
	$message = ( $core->get['message'] ) ? $core->get['message'] : null;

	if ( $module == 'courier' ) return order_courier( $core );
	if ( $module == 'delivery' ) return order_delivery( $core );
	if ( $module == 'security' ) return order_security( $core );

	if ( $module && $module != 'order' ) return false;

	switch ( $message ) {
    	case 'save':		$core->info( 'info', 'done_order_save' ); break;
    	case 'send':		$core->info( 'info', 'done_order_send' ); break;
    	case 'pack':		$core->info( 'info', 'done_order_pack' ); break;
    	case 'done':		$core->info( 'info', 'done_order_done' ); break;
    	case 'arrive':		$core->info( 'info', 'done_order_arrive' ); break;
    	case 'del':			$core->info( 'info', 'done_order_del' ); break;
    	case 'pickup':		$core->info( 'error', 'error_order_pickup' ); break;
    	case 'nocode':		$core->info( 'error', 'error_order_nocode' ); break;
    	case 'error':		$core->info( 'error', 'error_order_smth' ); break;
    	case 'access':		$core->info( 'error', 'access_denied' ); break;
	}

	// Edit order
	if ( $id ) {

		// Order, offer and variants
		$order = $core->db->row( "SELECT * FROM ".DB_ORDER." WHERE order_id = '$id' LIMIT 1" );
		if ( $order['user_id'] != $core->user->id && $order['comp_id'] != $core->user->comp && $core->user->level < 1 && !$core->user->call ) $core->go($core->url( 'mm', '', 'access' ));
		$offer = $core->wmsale->get( 'offer', $order['offer_id'] );
		$site = $order['site_id'] ? $core->wmsale->get( 'site', $order['site_id'], 'site_url' ) : false;
		$space = ( ( $core->user->level || $core->user->call ) && $order['space_id'] ) ? $core->wmsale->get( 'site', $order['space_id'], 'site_url' ) : false;
		$vars = ( $offer['offer_vars'] ) ? $core->wmsale->get( 'vars', $offer['offer_id'] ) : false;
		$oips = $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE order_ip = '".$order['order_ip']."'" . ( ( $core->user->level || $core->user->call ) ? '' : " AND comp_id = '".$order['comp_id']."'" ) );
		$ophs = $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE order_phone = '".$order['order_phone']."'" . ( ( $core->user->level || $core->user->call ) ? '' : " AND comp_id = '".$order['comp_id']."'" ) );
		$order['items'] = $order['order_items'] ? unserialize( $order['order_items'] ) : array();
		$user = $core->user->get( $order['wm_id'] );
		$callscheme = ( $callscheme = $core->wmsale->get( 'comp', $core->user->comp, 'callscheme' ) ) ? $callscheme : 'tel:+%s';

		$ophone = $core->db->row( "SELECT * FROM ".DB_PDB." WHERE `phone` = '".substr( $order['order_phone'], 1, 6 )."' LIMIT 1" );
		if ( $ophone ) {			$ophone['type'] = $ophone['operator'];
			$ophone['place'] = $ophone['region'];
			if ( $ophone['city'] ) $ophone['place'] .= ', ' . $ophone['city'];
		}

		$addr = $order['order_addr'];
		if ( $order['order_street'] ) $addr = $order['order_street'] . ', ' . $addr;
		if ( $order['order_city'] ) $addr = $order['order_city'] . ', ' . $addr;
		if ( $order['order_area'] ) $addr = $order['order_area'] . ', ' . $addr;

		// Store
		if ( $vars ) {        	$store = array();
        	$stores = $core->db->data( "SELECT var_id, store_count FROM ".DB_STORE." WHERE offer_id = '".$order['offer_id']."' AND comp_id = '".$order['comp_id']."'" );
        	foreach ( $stores as $s ) $store[$s['var_id']] = $s['store_count'];
		} else $store = (int) $core->db->field( "SELECT store_count FROM ".DB_STORE." WHERE offer_id = '".$order['offer_id']."' AND comp_id = '".$order['comp_id']."' LIMIT 1" );

		// Parameters
		if ( $offer['offer_paramurl'] && $order['order_meta'] ) {			$cache = sprintf( PATH_CACHE, md5( $order['order_meta'] ) );
			if ( !file_exists( $cache ) ) {				$post = unserialize( $order['order_meta'] );
				$form = curl( $offer['offer_paramurl'], $post );
				file_put_contents( $cache, $form );
			} else $form = file_get_contents( $cache );
		} else $form = null;

		// Page Header
		$core->mainline->add( $core->lang['orders_h'], $core->url( 'm', 'order' ) );
		$core->mainline->add( $offer['offer_name'] );
		$core->mainline->add( $order['order_name'] );
	    $core->header ();

		$core->tpl->load( 'body', 'order' );

		$core->tpl->vars( 'body', $offer );
		$core->tpl->vars( 'body', $order );

		$core->tpl->vars( 'body', array(
			'order'				=> $core->lang['order'],
			'save'				=> $core->lang['order_save'],
			'next'				=> $core->lang['order_save_next'],
			'u_edit'			=> $core->url( 'a', 'order-edit', $id ),
			'action'			=> $core->lang['order_work_action'],
			'mark'				=> $core->lang['order_marks'],
			'source'            => $core->lang['source'],
			'site'            	=> $core->lang['site'],
			'space'            	=> $core->lang['stat_spaces'],
			'store'				=> $core->lang['store'],
			'count'				=> $core->lang['count'],
			'price'				=> $core->lang['price'],
			'more_price'		=> $core->lang['order_more'],
			'total'				=> $core->lang['total'],
			'name'				=> $core->lang['name'],
			'fio'				=> $core->lang['username'],
			'address'			=> $core->lang['address'],
			'address_d'			=> $core->lang['order_addr_d'],
			'street'			=> $core->lang['street'],
			'city'				=> $core->lang['city'],
			'area'				=> $core->lang['area'],
			'phone'				=> $core->lang['phone'],
			'index'				=> $core->lang['index'],
			'present'			=> $core->lang['present'],
			'present_d'			=> $core->lang['present_d'],
			'call'				=> $core->lang['call'],
			'track'				=> $core->lang['track'],
			'checkaddr'			=> $core->lang['order_checkaddr'],
			'delivery'			=> $core->lang['deliver'],
			'discount'			=> $core->lang['discount'],
			'packdocs'			=> $core->lang['order_pack_docs'],
			'country'			=> $order['order_country'] ? $order['order_country'] : ( $order['geoip_country'] ? $order['geoip_country'] : 'zz' ),
			'callscheme'		=> $callscheme,
			'phone_call'		=> sprintf( $callscheme, $order['order_phone'] ),
			'phone_ok_c'		=> $order['order_phone_ok'] ? 'phone-ok' : 'phone-bad',
			'phone_ok_t'		=> $order['order_phone_ok'] ? 'ok' : '!!',
			'form'				=> $form,
			'status'			=> $core->lang['statuso'][$order['order_status']],
			'date'				=> smartdate( $order['order_time'] ),
			'u_addr'			=> '/addr.php?addr=',
			'u_spsr'			=> $core->url( 'a', 'order-spsr', 0 ),
			'u_rupost'			=> $core->url( 'a', 'order-rupost', 0 ),
			'fulladdr'			=> $addr,
			'r'					=> $core->server['HTTP_REFERER'],
			'site_url'			=> $site,
			'space_url'			=> $space,
			'paid_type'			=> $core->lang['order_paid'][$order['paid_ok']],
			'paid_date'			=> smartdate( $order['paid_time'] ),
			'paid_info'			=> $core->text->lines( $order['paid_from'] ),
			'u_phone'			=> $core->url( 'a', 'order-phone', 0 ) . '?phone=',
			'phone_info'		=> $ophone ? sprintf( "%s (%s)", $ophone['type'], $ophone['place'] ) : '',
			'order_ip'			=> int2ip( $order['order_ip'] ),
			'ipwarn'			=> ( $oips > 1 ) ? sprintf( $core->lang['order_ipwarn'], $core->url( 'm', 'order?s=' ) . int2ip($order['order_ip']), $oips ) : '',
			'phwarn'			=> ( $ophs > 1 ) ? sprintf( $core->lang['order_phwarn'], $core->url( 'm', 'order?s=' ) . $order['order_phone'], $ophs ) : '',
			'wm_name'			=> $order['wm_id'] ? ( $user['user_level'] ? '<b>'.$user['user_name'].'</b>' : $user['user_name'] ) : $core->lang['order_src_sh'],
			'wm_class'			=> $order['wm_id'] ? ( $order['ext_id'] ? 'ext' : ( $user['user_ban'] ? 'warn' : ( $user['user_warn'] ? 'ua' : ( $user['user_vip'] ? 'vip' : 'user' )) ) ) : 'search',
			'wm_src'			=> $order['flow_id'] ? sprintf( $core->lang['order_src_f'], $order['flow_id'] ) : ( $order['ext_src'] ? sprintf( $core->lang['order_src_e'], $user['user_id'], $order['ext_src'] ) : '' ),
		));

		// Spacer landing
		if ( $site ) $core->tpl->block( 'body', 'site' );
		if ( $space ) $core->tpl->block( 'body', 'space' );
		if ( $form ) $core->tpl->block( 'body', 'form' );
		if ( $order['paid_ok'] ) $core->tpl->block( 'body', 'paid' );
		if ( $order['order_file'] ) $core->tpl->block( 'body', 'file' );

		// WM info
		if ( $order['wm_id'] && !$order['ext_id'] ) {			$core->tpl->block( 'body', 'ofm', array( 'v' => $user['user_mail'] ) );
			if ( $user['user_wmr'] ) $core->tpl->block( 'body', 'ofw', array( 'v' => $user['user_wmr'] ) );
		}

		// Edit ability
		$canedit = ( $order['order_status'] > 1 && $order['order_status'] < 5 ) || $order['order_status'] == 6 || $order['order_status'] == 7;
		if ( $canedit ) {
			$core->tpl->block( 'body', 'edit' );
			if ( $offer['offer_delivery'] ) $core->tpl->block( 'body', 'edit.delivery' );
		} else {			$core->tpl->block( 'body', 'view' );
			if ( $order['order_comment'] ) $core->tpl->block( 'body', 'comment' );
		}

		// Package documents
		if ( $order['order_status'] == 6 && $order['order_delivery'] == 1 ) $core->tpl->block( 'body', 'docs', array( 'u' => $core->url( 'a', 'order-docs', $order['order_id'] ) ) );

		// Tracking code
		if ( $order['order_status'] == 7 || $order['order_status'] == 8 ) $core->tpl->block( 'body', 'track' );
		if ( ( $order['order_status'] > 7 && $order['order_status'] < 10 ) || $order['order_status'] == 11 ) $core->tpl->block( 'body', 'delpro', array(
			'cls'		=> $order['track_status'] ? ( ($order['order_status'] == 9) ? 'green' : 'blue' ) : 'red',
			'check'		=> sprintf( $core->lang['track_check'], smartdate( $order['track_check'] ) ),
			'info'		=> ( $order['track_status'] ) ? sprintf( "%s: %s", $order['track_date'], $order['track_status'] ) : $core->lang['track_wait'],
			'url'		=> sprintf( $core->lang['deliveru'][$order['order_delivery']], $order['track_code'] ),
		));

		// Variants of offer
		if ( $vars ) {			$ndprice = 0;			foreach ( $vars as $v ) {            	$core->tpl->block( 'body', 'item', array(
					'id'		=> $v['var_id'],
					'name'		=> $v['var_name'],
					'price'		=> $v['var_price'],
					'count'		=> (int) $order['items'][$v['var_id']],
					'total'		=> $v['var_price'] * (int) $order['items'][$v['var_id']],
					'store'		=> (int) $store[$v['var_id']],
            	));
            	if ( $canedit ) $core->tpl->block( 'body', 'item.edit' ); else $core->tpl->block( 'body', 'item.view' );
				$ndprice += $v['var_price'] * (int) $order['items'][$v['var_id']];
			}
		} else {           	$core->tpl->block( 'body', 'item', array(
				'id'		=> $offer['offer_id'],
				'name'		=> $offer['offer_name'],
				'price'		=> $offer['offer_price'],
				'count'		=> (int) $order['order_count'],
				'total'		=> $offer['offer_price'] * (int) $order['order_count'],
				'store'		=> (int) $store,
           	));
           	if ( $canedit ) $core->tpl->block( 'body', 'item.edit' ); else $core->tpl->block( 'body', 'item.view' );
			$ndprice = $offer['offer_price'] * (int) $order['order_count'];
		}

		// Discounts
		if ( $canedit ) {
			foreach ( $core->lang['discounts'] as $i => $n ) {
				$core->tpl->block( 'body', 'dcedit', array(
					'id'	=> $i,
	            	'name'	=> $n,
	            	'total'	=> ceil( $ndprice * ( (100 - $i) / 100 ) ),
	            	'check'	=> ( $i == $order['order_discount'] ) ? 'checked="checked"' : '',
				));
			}
		} else $core->tpl->block( 'body', 'dcview', array(
           	'name'	=> $core->lang['discounts'][$order['order_discount']],
           	'price'	=> $order['order_discount'],
           	'total'	=> $ndprice * ( (100 - $order['order_discount']) / 100 ),
		));

		// Delivery
		if ( $offer['offer_delivery'] ) {			$core->tpl->block( 'body', 'delivery' );
			if ( $canedit ) {				$core->tpl->block( 'body', 'delivery.moreedit' );				foreach ( $core->lang['delivery'] as $i => $n ) {					$core->tpl->block( 'body', 'delivery.edit', array(
						'id'	=> $i,
		            	'name'	=> $n,
		            	'price'	=> $core->lang['deliverp'][$i],
		            	'total'	=> $core->lang['deliverp'][$i],
		            	'check'	=> ( $i == $order['order_delivery'] ) ? 'checked="checked"' : '',
					));
				}
			} else {				if ( $order['order_more'] ) $core->tpl->block( 'body', 'delivery.moreview' );
				$core->tpl->block( 'body', 'delivery.view', array(
	            	'name'	=> $core->lang['delivery'][$order['order_delivery']],
	            	'price'	=> $core->lang['deliverp'][$order['order_delivery']],
	            	'total'	=> $core->lang['deliverp'][$order['order_delivery']],
				));
			}
		}

		// Actions
		if ( $order['order_status'] > 1 && $order['order_status'] < 5 ) {			$actions = array(
            	'order_call_basic'	=> array( 'ok' => $core->lang['order_call_ok'] ),
            	'order_call_re'	=> array(),
            	'order_call_no'	=> array(),
            	'cancel'		=> array(),
			);
			if ( $core->user->level || $core->user->shave ) $actions['order_call_basic']['shave'] = $core->lang['order_call_shave'];
			foreach ( $core->lang['recallo'] as $k => $v ) $actions['order_call_re']['re'.$k] = $v;
			foreach ( $core->lang['nocallo'] as $k => $v ) $actions['order_call_no']['no'.$k] = $v;
			foreach ( $core->lang['reasono'] as $k => $v ) $actions['cancel']['cancel'.$k] = $v;
		} elseif ( $order['order_status'] == 6 ) {
			$actions = array( 'order_pack_o' => $core->lang['packingo'] );
		} elseif ( $order['order_status'] == 7 ) {
			$actions = array( 'order_send_o' => $core->lang['sendingo'] );
		} elseif ( $order['order_status'] == 8 ) {
			$actions = array( 'order_deliver_o' => $core->lang['delivero'] );
		} elseif ( $order['order_status'] == 9 ) {
			$actions = array( 'order_pay_o' => $core->lang['payo'] );
		} else $actions = false;

		// Actions block
		if ( $actions ) {			$core->tpl->block( 'body', 'actions' );
			foreach ( $actions as $b => $a ) {
				$core->tpl->block( 'body', 'actions.block', array( 'name' => $core->lang[$b] ) );
				foreach ( $a as $v => $n ) $core->tpl->block( 'body', 'actions.block.a', array( 'n' => $n, 'v' => $v ) );
			}
		}

		// Marks
		if ( $order['order_status'] > 1 && $order['order_status'] < 5 ) {
			$marks = array(
				'banip'		=> sprintf( $core->lang['order_ban_ip'], int2ip( $order['order_ip'] ) ),
				'banphone'	=> sprintf( $core->lang['order_ban_phone'], $order['order_phone'] ),
			);

			if ( $oips > 1 ) {
				$ooips = $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE order_id != '$id' AND order_ip = '".$order['order_ip']."' AND order_status < 5 AND comp_id = '".$order['comp_id']."'" );
				if ( $ooips ) $marks['delip'] = sprintf( $core->lang['order_del_ip'], $ooips );
			}

			if ( $ophs > 1 ) {
				$oophs = $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE order_id != '$id' AND order_phone = '".$order['order_phone']."' AND order_status < 5 AND comp_id = '".$order['comp_id']."'" );
				if ( $oophs ) $marks['delphone'] = sprintf( $core->lang['order_del_phone'], $oophs );
			}

		} else $marks = array();

		// Checking marks
		if ( $order['order_status'] > 1 && $order['order_status'] < 10 && $order['order_status'] != 5 ) {
			if ( $order['order_check'] ) {				$marks['uncheck'] = $core->lang['order_uncheck'];
			} else $marks['check'] = $core->lang['order_tocheck'];
		}

		// Marks block
		if ( $marks ) {			$core->tpl->block( 'body', 'marks' );
			foreach ( $marks as $v => $n ) $core->tpl->block( 'body', 'marks.mk', array( 'n' => $n, 'v' => $v ) );
		}

		// Button blocks
		if ( $order['order_status'] == 1 ) $core->tpl->block( 'body', 'pickup', array(
			'u' => $core->url( 'a', 'order-pickup', $id ),
			't'	=> $core->lang['order_pick_up'],
			'c'	=> $core->lang['order_pick_confirm'],
		));
		if ( $order['order_status'] != 5 && $order['order_status'] > 1 && $order['order_status'] < 10 ) $core->tpl->block( 'body', 'buttons' );

		if ( $order['geoip_country'] ) {			$geoip = $order['geoip_city'] ? $order['geoip_city'] : '';
			if ( $order['geoip_region'] ) $geoip .= ', ' . $order['geoip_region'];
			if ( $order['geoip_district'] ) $geoip .= ', ' . $order['geoip_district'];
			$geoip = trim( $geoip, ', ' );
			if ( ! $geoip ) $geoip = $order['geoip_country'];
        	if ( $order['geoip_lat'] && $order['geoip_lng'] ) $geoip = '<a target="_blank" href="http://maps.yandex.ru/?ll='.$order['geoip_lng'].'%2C'.$order['geoip_lat'].'">'.$geoip.'</a>';
        	$core->tpl->vars( 'body', array( 'order_country' => $geoip ) );
		}

		$core->tpl->output( 'body' );

		$core->footer ();

	} else {

		$where = array();

		// User filter
		if ( $core->user->level || $core->user->call ) {
			if ( isset( $core->get['c'] ) && $core->get['c'] ) {				$c = (int) $core->get['c'];
				$where[] = "comp_id = '$c'";
			} else $c = false;
		} else {			if ( isset( $core->get['a'] ) && $a = (int) $core->get['a'] ) {
				$where[] = "user_id = '".$core->user->id."'";
			} else $where[] = "comp_id = '".$core->user->comp."'";
			$manager = $core->wmsale->get( 'mans', $core->user->comp ); $manager[0] = '———';
		}

		// WebMaster and Source Filters
		if ( isset( $core->get['wm'] ) && $core->get['wm'] ) {
			$wm = (int) $core->get['wm'];
			$where[] = "wm_id = '$wm'";
		} else $wm = false;
		if ( isset( $core->get['src'] ) && $core->get['src'] ) {
			$src = $core->text->link( $core->get['src'] );
			$where[] = "ext_src = '$src'";
		} else $src = false;

		// Search
		if ( isset( $core->get['s'] ) && $core->get['s'] ) {			$s = $core->text->line( $core->get['s'] );
			if ( preg_match( '#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#i', $s ) && $ips = ip2int( $s ) ) {
            	$where[] = " order_ip = '$ips' ";
			} elseif ( preg_match( '#^[0-9]{11}$#i', $s ) ) {
            	$where[] = " order_phone = '$s' ";
			} else {
				require_once PATH_CORE . 'search.php';
				$search = new SearchWords( $core->get['s'] );
				if ( $s = $search->get() ) {					$where[] = $search->field(array( 'order_name', 'order_addr', 'order_street', 'order_city', 'order_area' ));
				} else $s = false;
			}
		} else $s = false;

		// Status filtering
		if ( isset( $core->get['f'] ) && $core->get['f'] != '' ) {
			$f = (int) $core->get['f'];
			if ( $f < 0 ) {
				switch ( $f ) {
					case -1:	$where[] = "order_status NOT IN ( 5, 12 )"; break;
					case -2:	$where[] = "order_status < 5"; break;
					case -3:	$where[] = "order_status > 5 AND order_status < 12"; break;
				 	default:	$f = '';
				}
			} else $where[] = "order_status = '$f'";
		} else $f = '';

		// Offer filtering
		if ( isset( $core->get['o'] ) && $core->get['o'] ) {
			$o = (int) $core->get['o'];
			$where[] = "offer_id = '$o'";
		} else $o = false;

		// Date filtering
		if ( $d = $core->get['d'] ) {
			$dd = explode( '-', $d );
			$ds = mktime( 0, 0, 0, $dd[1], $dd[2], $dd[0] );
			$de = mktime( 23, 59, 59, $dd[1], $dd[2], $dd[0] );
			$where[] = "( order_time BETWEEN '$ds' AND '$de' )";
		} else $d = false;

		$where = count( $where ) ? implode( ' AND ', $where ) : '1';

		$csv = ( $core->get['mode'] == 'csv' ) ? 1 : 0;
		$ipsl = $phsl = array();

		if ( ! $csv ) {

			$sh = 20; $st = $sh * ( $page - 1 );
			$orders = $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE $where" );
			$order = $orders ? $core->db->data( "SELECT * FROM ".DB_ORDER." WHERE $where ORDER BY order_status ASC, order_time DESC LIMIT $st, $sh" ) : false;

			foreach ( $order as &$ooo ) {
				$ipsl[] = $ooo['order_ip'];
				$phls[] = $ooo['order_phone'];
			} unset ( $ooo );

		} else $order = $core->db->data( "SELECT * FROM ".DB_ORDER." WHERE $where ORDER BY order_status ASC, order_time DESC" );

		$company = $core->user->comp ? $core->wmsale->get( 'comp', $core->user->comp ) : false;
		$offer = $core->wmsale->get( 'offers' );
		$vars = array();

		// Check for the bans
		if ( $ipls || $phls ) {			require_once PATH . 'lib/ban.php';
			$banip = check_ip_bans( $core, $ipls );
			$banph = check_phone_bans( $core, $phls );
		} else $banip = $banph = array();

		$core->mainline->add( $core->lang['orders_h'], $core->url( 'm', 'order' ) );
	    if ( ! $csv ) $core->header ();

		$core->tpl->load( 'body', $csv ? 'csv-index' : 'index' );

	    $core->tpl->vars ('body', array (
			'title'			=> $core->lang['orders_h'],
			'text'			=> $core->text->lines( $core->lang['orders_t'] ),
			'offer'			=> $core->lang['offer'],
			'phone'			=> $core->lang['phone'],
			'name'			=> $core->lang['username'],
			'address'		=> $core->lang['address'],
			'time'			=> $core->lang['time'],
			'price'			=> $core->lang['price'],
			'status'		=> $core->lang['status'],
			'action'		=> $core->lang['action'],
			'pay'			=> $core->lang['pay'],
			'edit'			=> $core->lang['edit'],
			'del'			=> $core->lang['del'],
			'confirm'		=> $core->lang['confirma'],
			'call_confirm'	=> $core->lang['order_call_confirm'],
			'call_default'	=> $core->lang['order_call_action'],
			'call_ok'		=> $core->lang['order_call_ok'],
			'call_re'		=> $core->lang['order_call_re'],
			'call_no'		=> $core->lang['order_call_no'],
			'pickup'		=> $core->lang['order_pick_up'],
			'pick_confirm'	=> $core->lang['order_pick_confirm'],
			'packed'		=> $core->lang['order_packed'],
			'packdocs'		=> $core->lang['order_pack_docs'],
			'pack_confirm'	=> $core->lang['order_pack_confirm'],
			'track_code'	=> $core->lang['track_code'],
			'track_send'	=> $core->lang['track_send'],
			'track_confirm'	=> $core->lang['track_confirm'],
			'info'			=> $core->lang['inf'],
			'work'			=> $core->lang['order_work'],
			'pack'			=> $core->lang['order_pack'],
			'cancel'		=> $core->lang['order_cancel'],
			'later'			=> $core->lang['order_later'],
			'showall'		=> $core->lang['order_showall'],
			'company'		=> $core->lang['company'],
			'd'				=> $d,
			's'				=> $search ? $search->get() : $s,
			'wm'			=> $wm,
			'src'			=> $src,
			'pages'			=> pages ( $core->url( 'm', '?' ) . ( $f ? 'f='.$f.'&' : '' ) . ( $d ? 'd='.$d.'&' : '' ) . ( $s ? 's='.$s.'&' : '' ) . ( $a ? 'a='.$a.'&' : '' ) . ( $c ? 'c='.$c.'&' : '' ) . ( $o ? 'o='.$o.'&' : '' ) . ( $wm ? 'wm='.$wm.'&' : '' ) . ( $src ? 'src='.$src.'&' : '' ), $orders, $sh, $page ),
			'shown'			=> sprintf( $core->lang['shown'], $st+1, min( $st+$sh, $orders ), $orders ),
			'filter'		=> $core->lang['filter'],
			'date'			=> $core->lang['date'],
			'search'		=> $core->lang['search'],
			'find'			=> $core->lang['find'],
			'u_pickup'		=> $core->url( 'a', 'order-pickup', '' ),
			'o_pickup'		=> $core->lang['order_pick_up_smth'],
			'u_courier'		=> $core->url( 'm', 'courier' ),
			'courier'		=> $core->lang['order_courier'],
			'u_csv'			=> $core->url( 'm', '?mode=csv&' ) . ( $f ? 'f='.$f.'&' : '' ) . ( $d ? 'd='.$d.'&' : '' ) . ( $s ? 's='.$s.'&' : '' ) . ( $a ? 'a='.$a.'&' : '' ) . ( $c ? 'c='.$c.'&' : '' ) . ( $o ? 'o='.$o.'&' : '' ) . ( $wm ? 'wm='.$wm.'&' : '' ) . ( $src ? 'src='.$src.'&' : '' ),
			'esend'			=> $core->lang['order_esend'],
			'es_confirm'	=> $core->lang['order_es_confirm'],
			'o_1'			=> ( $f == -1 ) ? 'selected="selected"' : '',
			'o_2'			=> ( $f == -2 ) ? 'selected="selected"' : '',
			'o_3'			=> ( $f == -3 ) ? 'selected="selected"' : '',
	    ));

	    if ( $company['comp_spsr'] ) $core->tpl->block( 'body', 'couriers' );
	    if ( $core->user->work < 2 ) $core->tpl->block( 'body', 'pickitup' );

		foreach ( $core->lang['statuso'] as $i => $st ) {
			$core->tpl->block( 'body', 'status', array(
				'name'		=> $st,
				'value'		=> $i,
				'select'	=> ( $f != '' && $f == $i ) ? 'selected="selected"' : '',
			));
		}

		$comp = $core->wmsale->get( 'comps' );
		if ( $core->user->level || $core->user->call ) {			$core->tpl->block( 'body', 'comps' );
			foreach ( $comp as $ci => $cn ) {				$core->tpl->block( 'body', 'comps.c', array(
					'name'		=> $cn,
					'value'		=> $ci,
					'select'	=> ( $c == $ci ) ? 'selected="selected"' : '',
				));
			}
		} else $core->tpl->block( 'body', 'all', array( 'a' => $a ? 'checked="checked"' : '' ) );

		foreach ( $offer as $i => $of) {
			$core->tpl->block( 'body', 'offer', array(
				'name'		=> $of,
				'value'		=> $i,
				'select'	=> ( $o == $i ) ? 'selected="selected"' : '',
			));
		}

		$callscheme = ( $callscheme = $core->wmsale->get( 'comp', $core->user->comp, 'callscheme' ) ) ? $callscheme : 'tel:+%s';
		if ( $order ) foreach ( $order as &$r ) {

			$addr = $r['order_addr'];
			if ( $r['order_street'] ) $addr = $r['order_street'] . ', ' . $addr;
			if ( $r['order_city'] ) $addr = $r['order_city'] . ', ' . $addr;
			if ( $r['order_area'] ) $addr = $r['order_area'] . ', ' . $addr;
			$addr = trim( $addr, ', ' );

			$uid = $r['wm_id'];
			$user = $uid ? $core->user->get( $uid ) : array();

			$core->tpl->block( 'body', 'ord', array(
				'oid'			=> $r['offer_id'],
				'offer'			=> $offer[$r['offer_id']],
				'id'			=> $r['order_id'],
				'ip'			=> int2ip( $r['order_ip'] ),
				'ip_class'		=> $banip[$r['order_ip']] ? ( ($banip[$r['order_ip']]) < 10 ? 'yellow' : 'red' ) : 'green',
				'country'		=> $r['order_country'] ? $r['order_country'] : ( $r['geoip_country'] ? $r['geoip_country'] : 'zz' ),
				'name'			=> $search ? $search->highlight( $r['order_name'] ) : $r['order_name'] ,
				'addr'			=> $search ? $search->highlight( $addr ) : $addr,
				'index'			=> $r['order_index'],
				'comment'		=> $r['order_comment'],
				'phone'			=> $search ? $search->highlight( $r['order_phone'] ) : $r['order_phone'],
				'phone_call'	=> sprintf( $callscheme, $r['order_phone'] ),
				'phone_ok'		=> $r['order_phone_ok'] ? 'ok' : 'bad',
				'phone_class'	=> $banph[$r['order_phone']] ? ( ($banph[$r['order_phone']]) < 10 ? 'yellow' : 'red' ) : 'green',
				'count'			=> $r['order_count'],
				'price'			=> rur( $r['order_price'] ),
				'price_csv'		=> (int) $r['order_price'],
				'time'			=> smartdate( $r['order_time'] ),
				'stid'			=> $r['order_status'],
				'status'		=> $core->lang['statuso'][$r['order_status']],
				'edit'			=> $core->url ( 'i', 'order', $r['order_id'] ),
				'actcls'		=> ( $r['order_status'] < 5 || $r['order_status'] == 7 ) ? 'cb' : '',
				'manager'		=> $manager[$r['user_id']],
				'paid'			=> $r['paid_ok'],
				'paidinfo'		=> $core->lang['order_paid'][$r['paid_ok']] . ( $r['paid_time'] ? ' - '.smartdate( $r['paid_time'] ) : '' ),
				'calls'			=> ( $r['order_calls'] ) ? sprintf( ' <small title="%s" class="red">(%s)</small>', $core->lang['order_calls'], $r['order_calls'] ) : '',
				'delivery'		=> $r['order_delivery'],
				'delivern'		=> $core->lang['delivers'][$r['order_delivery']],
				'uid'			=> $uid,
				'uname'			=> $uid ? ( $user['user_level'] ? '<b>'.$user['user_name'].'</b>' : $user['user_name'] ) : $core->lang['order_src_sh'],
				'uclass'		=> $r['order_check'] ? 'warn' : ( $uid ? ( $r['ext_id'] ? 'ext' : ( $user['user_vip'] ? 'vip' : 'user' ) ) : 'search' ),
			));

			if ( $r['order_status'] == 1 ) {				$core->tpl->block( 'body', 'ord.pickup', array( 'u' => $core->url( 'a', 'order-pickup', $r['order_id'] ) ) );
				if ( $core->user->level || $core->user->call ) {                	$core->tpl->block( 'body', 'ord.pickup.move', array( 'u' => $core->url( 'a', 'order-move', $r['order_id'] ) ) );
					foreach ( $comp as $v => $n )  $core->tpl->block( 'body', 'ord.pickup.move.comp', array( 'val' => $v, 'name' => $n ));
				}
			}

			if ( $r['order_status'] > 1 && $r['order_status'] < 5 ) {               	$core->tpl->block( 'body', 'ord.call', array( 'action' => $core->url( 'a', 'order-call', $r['order_id'] ) ));
				foreach ( $core->lang['recallo'] as $v => $n )  $core->tpl->block( 'body', 'ord.call.re', array( 'val' => $v, 'name' => $n ));
				foreach ( $core->lang['nocallo'] as $v => $n )  $core->tpl->block( 'body', 'ord.call.no', array( 'val' => $v, 'name' => $n ));
				foreach ( $core->lang['reasono'] as $v => $n )  $core->tpl->block( 'body', 'ord.call.cancel', array( 'val' => $v, 'name' => $n ));
			}

			if ( $r['order_status'] == 5 ) $core->tpl->block( 'body', 'ord.cancel', array( 'reason' => $r['order_reason'] ? $core->lang['reasono'][$r['order_reason']] : ( $r['order_comment'] ? sprintf( $core->lang['noreason_comment'], $r['order_comment'] ) :  $core->lang['noreason'] ) ) );

			if ( $r['order_status'] == 6 ) {				$items = $r['order_items'] ? unserialize( $r['order_items'] ) : false;
				$iline = '';
				if ( $items ) {					if (!count( $vars[$r['offer_id']] )) {						$vrs = $core->wmsale->get( 'vars', $r['offer_id'] );
						$vars[$r['offer_id']] = array(); foreach ( $vrs as $w ) $vars[$r['offer_id']][ $w['var_id'] ] = $w['var_short'];
					}
					foreach ( $items as $k => $x ) $iline .= ' ' . $vars[$r['offer_id']][$k] . ': ' . $x . ' ';
				}
				$core->tpl->block( 'body', 'ord.pack', array( 'docs' => $core->url( 'a', 'order-docs', $r['order_id'] ), 'done' => $core->url( 'a', 'order-packed', $r['order_id'] ), 'items' => $iline ));
				if ( $r['order_delivery'] == 1 ) $core->tpl->block( 'body', 'ord.pack.doc' );
			}

			if ( $r['order_status'] == 7 ) {				$core->tpl->block( 'body', 'ord.send', array( 'u' => $core->url( 'a', 'order-send', $r['order_id'] ) ) );
				if ( $r['order_delivery'] > 1 ) $core->tpl->block( 'body', 'ord.esend', array(
					'u'		=> $core->url( 'a', 'order-esend', $r['order_id'] ),
					'nc'	=> $r['order_courier'] ? 'new' : 'deliver',
					'nu'	=> $core->url( 'a', $r['order_courier'] ? 'order-snew' : 'order-sold', $r['order_id'] ),
					'nt'	=> $r['order_courier'] ? $core->lang['order_spsr_new'] : $core->lang['order_spsr_old'],
				));
			}

			if ( $r['order_status'] == 8 || $r['order_status'] == 9 || $r['order_status'] == 11 ) {				$core->tpl->block( 'body', 'ord.track', array(
					'cls'		=> $r['track_status'] ? ( ($r['order_status'] == 9) ? 'green' : 'blue' ) : 'red',
					'check'		=> sprintf( $core->lang['track_check'], smartdate( $r['track_check'] ) ),
					'info'		=> ( $r['track_status'] ) ? sprintf( "%s: %s", $r['track_date'], $r['track_status'] ) : $core->lang['track_wait'],
					'url'		=> sprintf( $core->lang['deliveru'][$r['order_delivery']], $r['track_code'] ),
				));
				if ( $r['order_status'] == 8 ) $core->tpl->block( 'body', 'ord.track.confirm', array( 'c' => $core->lang['order_arrive_conf'], 't' => $core->lang['order_arrived'], 'u' => $core->url( 'a', 'order-arrive', $r['order_id'] ) ) );
				if ( $r['order_status'] == 9 ) $core->tpl->block( 'body', 'ord.track.confirm', array( 'c' => $core->lang['order_payd_conf'], 't' => $core->lang['order_payd'], 'u' => $core->url( 'a', 'order-done', $r['order_id'] ) ) );
			}

			if ( $core->user->level || $core->user->call ) {				$core->tpl->block( 'body', 'ord.comp', array( 'id' => $r['comp_id'], 'name' => $comp[$r['comp_id']] ));
			} else $core->tpl->block( 'body', 'ord.ip' );

		} unset ( $r, $order );

		if ( $core->user->work < 2 && $page < 2 && ! ( $s || $f || $d || $a || $wm || $src ) ) {

			$recall = ( $core->user->call ) ? $core->db->data( "SELECT * FROM ".DB_ORDER." WHERE order_status IN ( 3, 4 ) AND order_recall < '".time()."'" ) : $core->db->data( "SELECT * FROM ".DB_ORDER." WHERE order_status IN ( 3, 4 ) AND order_recall < '".time()."' AND ( comp_id = '".$core->user->comp."' OR user_id = '".$core->user->id."' )" );

			if ( $recall ) {
				$callscheme = ( $callscheme = $core->wmsale->get( 'comp', $core->user->comp, 'callscheme' ) ) ? $callscheme : 'tel:+%s';
				$core->tpl->block( 'body', 'recall', array( 'text' => $core->text->lines( $core->lang['recall_t'] ) ));
				foreach ( $recall as &$r ) {
					$core->tpl->block( 'body', 'recall.ord', array(
						'offer'			=> $offer[$r['offer_id']],
						'id'			=> $r['order_id'],
						'name'			=> $search ? $search->highlight( $r['order_name'] ) : $r['order_name'] ,
						'addr'			=> $search ? $search->highlight( $r['order_addr'] ) : $r['order_addr'],
						'index'			=> $r['order_index'],
						'phone'			=> $search ? $search->highlight( $r['order_phone'] ) : $r['order_phone'],
						'phone_call'	=> sprintf( $callscheme, $r['order_phone'] ),
						'phone_ok'		=> $r['order_phone_ok'] ? 'ok' : 'bad',
						'count'			=> $r['order_count'],
						'price'			=> rur( $r['order_price'] ),
						'time'			=> smartdate( $r['order_time'] ),
						'stid'			=> $r['order_status'],
						'status'		=> $core->lang['statuso'][$r['order_status']],
						'calls'			=> ( $r['order_calls'] ) ? sprintf( ' <small title="%s" class="red">(%s)</small>', $core->lang['order_calls'], $r['order_calls'] ) : '',
						'action' 		=> $core->url( 'a', 'order-call', $r['order_id'] ),
						'edit'			=> $core->url ( 'i', 'order', $r['order_id'] ),
					));

					foreach ( $core->lang['recallo'] as $v => $n )  $core->tpl->block( 'body', 'recall.ord.re', array( 'val' => $v, 'name' => $n ));
					foreach ( $core->lang['nocallo'] as $v => $n )  $core->tpl->block( 'body', 'recall.ord.no', array( 'val' => $v, 'name' => $n ));
					foreach ( $core->lang['reasono'] as $v => $n )  $core->tpl->block( 'body', 'recall.ord.cancel', array( 'val' => $v, 'name' => $n ));

				} unset ( $r, $recall );
			}

		}

		if ( $csv ) {
    		header( 'Content-type: text/csv; charset=windows-1251' );
    		header( 'Content-disposition: attachment; filename=orders.csv' );
			$core->tpl->output( 'body', 'windows-1251//IGNORE' );
		} else {			$core->tpl->output( 'body' );
			$core->footer ();
		}

	}

    $core->_die();

}