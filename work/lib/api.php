<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / api.php
 *  Description:	API functions
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

function api ( $core, $app, $func, $type, $id ) {
	$apps = array(
//		'ext'	=> array( 'example' ),
		'wm'	=> array( 'flows', 'offers', 'pub', 'land', 'sites', 'stats', 'lead', 'sources', 'add', 'edit', 'del' ),
		'sale'	=> array( 'list', 'view', 'edit' )
	);
	$na = array( 'wm-pub', 'wm-land' );

	$app	= $core->text->link( $app );
	$func	= $core->text->link( $func );
	$type	= $core->text->link( $type );
	if ( $id ) {		$ids = explode( '-', $id, 2 );
		$id  = (int) $ids[0];
		$key = $core->text->link( $ids[1] );
	} else $id = $key = false;

	if ( in_array( $func, $apps[$app] ) ) {

		if ( $id ) {			$uk = $core->user->get( $id, 'user_api' );
			if ( $uk != $key ) {
				$ck = hash_hmac( 'sha1', http_build_query( $core->post ), $uk );
				$auth = ( $ck == $key ) ? 1 : 0;
			} else $auth = 1;
		} else $auth= in_array( "$app-$func", $na ) ? 1 : 0;

		if ( $auth ) {			$fname = 'api_' . $app . '_' . $func;
			if ( function_exists( $fname ) ) {            	$result = $fname( $core, $id );
			} else $result = array( 'status' => 'error', 'error' => 'func' );
		} else $result = array( 'status' => 'error', 'error' => 'key' );

	} else $result = array( 'status' => 'error', 'error' => 'app' );

	switch ( $type ) {		case 'text':	echo http_build_query( $result );	break;
		case 'raw':		echo $result;						break;
		case 'xml':		echo array2xml( $result, $func );	break;
		case 'json':	echo defined('JSON_UNESCAPED_UNICODE') ? json_encode( $result, JSON_UNESCAPED_UNICODE ) : json_encode( $result ); break;
		default:		echo serialize( $result );
	}

}

/* // External connection example
function api_ext_example ( $core, $o = false ) {

	$idsg = $core->post['ids'] ? explode( ',', $core->post['ids'] ) : ( $core->get['ids'] ? explode( ',', $core->get['ids'] ) : array() );
	$ids = array(); foreach ( $idsg as $i ) if ( $i = (int) $i ) $ids[] = $i;
	$idsl = implode( ',', $ids );
	$orders = $core->db->data( "SELECT order_webstat, ext_uid FROM ".DB_ORDER." WHERE ext_id = 1 AND ext_uid IN ( $idsl )" );

	$result = array();
	foreach ( $orders as $o ) {
		$result[ (int) $o['ext_uid'] ] = array(
			'id'			=> (int) $o['ext_uid'],
			'status'		=> (int) $o['order_webstat'],
			'status_text'	=> $core->lang['statuso'][$o['order_webstat']],
		);
	}

	return $result;

}
*/

//
// WebMaster API
//

// Add ew flow
function api_wm_add ( $core, $user ) {

	$offer	= (int) $core->post['offer'];
	$name 	= $core->text->line( $core->post['name'] );

	require_once PATH . 'lib/webmaster.php';
	$oid = webmaster_flow_add ( $core, $user, $offer, $name );

  	if ( $oid ) {
       	if ( $oid > 0 ) {
            return array( 'status' => 'ok', 'id' => $oid );
       	} else return array( 'status' => 'error', 'error' => 'offer-inactive' );
  	} else return array( 'status' => 'error', 'error' => 'request-error' );

}

// Edit flow name
function api_wm_edit ( $core, $user ) {

	$flow	= (int) $core->post['flow'];
	$name 	= $core->text->line( $core->post['name'] );

	require_once PATH . 'lib/webmaster.php';
	$result = webmaster_flow_edit ( $core, $user, $flow, $name );

  	if ( $result ) {
       	if ( $result > 0 ) {
            return array( 'status' => 'ok' );
       	} else return array( 'status' => 'error', 'error' => 'access-denied' );
  	} else return array( 'status' => 'error', 'error' => 'request-error' );

}

// Delete the flow
function api_wm_del ( $core, $user ) {

	$flow	= (int) $core->post['flow'];

	require_once PATH . 'lib/webmaster.php';
	$result = webmaster_flow_del ( $core, $user, $flow );

  	if ( $result ) {
       	if ( $result > 0 ) {
            return array( 'status' => 'ok' );
       	} else return array( 'status' => 'error', 'error' => 'access-denied' );
  	} else return array( 'status' => 'error', 'error' => 'request-error' );

}

// Flows list
function api_wm_flows ( $core, $user ) {

	$oid = (int) $core->post['offer'];
	$flows = $core->db->data( "SELECT * FROM ".DB_FLOW." WHERE user_id = '$user'" . ( $oid ? " AND offer_id = '$oid' " : '' ) );
	$offer = $core->wmsale->get( 'offers' );

	$result = array();
	foreach ( $flows as $f ) {		$result[ (int) $f['flow_id'] ] = array(
			'id'		=> (int) $f['flow_id'],
			'offer'		=> (int) $f['offer_id'],
			'offername'	=> $offer[$f['offer_id']],
			'name'		=> $f['flow_name'],
			'epc'		=> sprintf( "%0.2f", $f['flow_epc'] ),
			'cr'		=> sprintf( "%0.2f", $f['flow_convert'] * 100 ),
			'total'		=> (int) $f['flow_total'],
		);
	} unset ( $f, $flows );

	return $result;

}

// Offers list
function api_wm_offers ( $core, $user ) {

	$offers = $core->db->data( "SELECT * FROM ".DB_OFFER." WHERE offer_active = 1 ORDER BY offer_name ASC" );
	$flows = $core->db->icol( "SELECT offer_id, COUNT(*) FROM ".DB_FLOW." WHERE user_id = '$user' GROUP BY offer_id" );
	$vip = $core->user->get( $user, 'user_vip' );
	$result = array();

	foreach ( $offers as &$o ) {
		$result[ (int) $o['offer_id'] ] = array(
			'id'		=> (int) $o['offer_id'],
			'name'		=> $o['offer_name'],
			'descr'		=> $o['offer_text'],
			'price'		=> (int) $o['offer_price'],
			'wm'		=> (int) $vip ? $o['offer_wm_vip'] : $o['offer_wm'],
			'epc'		=> sprintf( "%0.2f", ( $vip ? $o['offer_wm_vip'] : $o['offer_wm'] ) * $o['offer_convert'] ),
			'cr'		=> sprintf( "%0.2f", $o['offer_convert'] * 100 ),
			'flows'		=> (int) $flows[$o['offer_id']],
			'geo'		=> $o['offer_country'] ? $o['offer_country'] : 'ru',
			'male'		=> sprintf( "%0.1f", $o['stat_m'] ),
			'female'	=> sprintf( "%0.1f", $o['stat_f'] ),
		);
	} unset ( $o, $offers );

	return $result;

}

// Offers public list
function api_wm_pub ( $core, $user = false ) {

	$offers = $core->db->data( "SELECT * FROM ".DB_OFFER." WHERE offer_active = 1 ORDER BY offer_name ASC" );
	$defurl = $core->db->icol( "SELECT offer_id, site_url FROM ".DB_SITE." WHERE site_type = 0 AND site_default = 1" );
	$result = array();

	foreach ( $offers as &$o ) {

		if ( ! $defurl[$o['offer_id']] ) {			$uu = $core->db->field( "SELECT site_url FROM ".DB_SITE." WHERE site_type = 0 AND offer_id = '".$o['offer_id']."' LIMIT 1" );
		} else $uu = $defurl[$o['offer_id']];

		$result[ (int) $o['offer_id'] ] = array(
			'id'		=> (int) $o['offer_id'],
			'name'		=> $o['offer_name'],
			'descr'		=> $o['offer_text'],
			'url'		=> $uu,
			'price'		=> (int) $o['offer_price'],
			'image'		=> 'http://'.$core->server['HTTP_HOST'].'/data/offer/'.$o['offer_id'].'.jpg',
			'imgt'		=> filemtime( PATH . 'data/offer/'.$o['offer_id'].'.jpg' ),
		);

	} unset ( $o, $offers );

	return $result;

}

// Landings
function api_wm_land ( $core, $user = false ) {
	$id = (int) $core->get['oid'];
	if ( ! $id ) return false;

	$lands = $core->wmsale->get( 'lands', $id );
	$space = $core->wmsale->get( 'space', $id );
	$default = 0;
	$elands = $espace = array();
	foreach ( $lands as $l ) {
		if ( ! $default ) $default = $l['site_id'];
		if ( $l['site_default'] ) $default = $l['site_id'];
		$elands[$l['site_id']] = 'http://' . $l['site_url'] . '/?';
	}
	foreach ( $space as $l ) $espace[$l['site_url']] = (int) $l['site_id'];

	return array( 'default' => $default, 'lands' => $elands, 'space' => $espace );

}

// Sites list
function api_wm_sites ( $core, $user = false ) {
	$offer = (int) $core->post['offer'];
	$vip = $core->user->get( $user, 'user_vip' );
	$price = $core->wmsale->get( 'offer', $offer, $vip ? 'offer_wm_vip' : 'offer_wm' );
	$sites = $core->wmsale->get( 'sites', $offer );

	$result = array( 'land' => array(), 'space' => array() );
	foreach ( $sites as $s ) {		$result[ $s['site_type'] ? 'space' : 'land' ][ (int) $s['site_id'] ] = array(
			'id'	=> (int) $s['site_id'],
			'url'	=> 'http://' . $s['site_url'] . '/',
			'epc'	=> sprintf( "%0.2f", $price * $s['site_convert'] ),
			'cr'	=> sprintf( "%0.2f", $s['site_convert'] * 100 ),
		);
	} unset ( $sites, $s );

	return $result;

}

// Statistics
function api_wm_stats ( $core, $user ) {

	$today = date( 'Ymd' );
	$week1 = date( 'Ymd', strtotime( '-1 week' ) );

	extract(params( $core, array( 'to' => 'date', 'from' => 'date', 'offer', 'flow' ) ));
	if ( ! $to || $to > $today ) $to = $today;
	if ( $from > $to ) $from = $to;
	if ( ! $from ) $from = $week1;

	require_once PATH . 'lib/webmaster.php';
	list( $offer, $flow, $stats ) = webmaster_clicks( $core, $user, $from, $to, $flow, $offer );

	return $stats;

}


// Statistics
function api_wm_lead ( $core, $user ) {

	$where = array(); $result = array();
	extract(params( $core, array( 'day' => 'date', 'offer', 'flow', 'site', 'status' ) ));

	if ( ! $day ) $day = date( 'Ymd' );
	$d = date2form( $day );
	$ds = strtotime( $d . ' 00:00:00' );
	$de = strtotime( $d . ' 23:59:59' );
	$where[] = " order_time BETWEEN '$ds' AND '$de' ";

	if ( $offer ) $where[] = " offer_id = '$offer' ";
	if ( $site ) $where[] = " site_id = '$site' ";

	switch ( $status ) {
		case 'w':	$where[] = " order_webstat < 5 "; break;
		case 'c':	$where[] = " order_webstat IN ( 5, 12 ) "; break;
		case 'a':	$where[] = " order_webstat BETWEEN 6 AND 11 "; break;
	}

	$flows = $core->db->col( "SELECT flow_id FROM ".DB_FLOW." WHERE user_id = '$user' ".( $o ? " AND offer_id = '$o' " : '' )." ORDER BY flow_name ASC" );
	if ( $flow ) {    	if ( in_array( $flow, $flows ) ) {        	$where = " flow_id = '$flow' ";
    	} else $where[] = " flow_id IN ( ".implode( ',',  $flows  )." ) ";
	} else $where[] = " flow_id IN ( ".implode( ',',  $flows  )." ) ";

	$where = implode( ' AND ', $where );
	$order = $core->db->data( "SELECT order_webstat, order_reason, order_time, offer_id, flow_id, site_id, space_id, order_ip, utm_id, utm_cn, utm_src FROM ".DB_ORDER." WHERE $where ORDER BY order_id DESC" );

	foreach ( $order as &$r ) {		$result[] = array(
			'time'			=> $r['order_time'],
			'status'		=> ( $r['order_webstat'] < 6 || $r['order_webstat'] == 12 ) ? $r['order_webstat'] : 10,
			'status_text'	=> ( $r['order_webstat'] < 6 || $r['order_webstat'] == 12 ) ? $core->lang['statuso'][$r['order_webstat']] : $core->lang['statusok'],
			'reason'		=> $r['order_reason'],
			'reason_text'	=> $r['order_reason'] ? $core->lang['reasono'][$r['order_reason']] : ( ( $r['order_webstat'] == 5 || $r['order_webstat'] == 12 ) ? $core->lang['noreason'] : ''  ),
			'offer'			=> $r['offer_id'],
			'offer_name'	=> $core->wmsale->get( 'offer', $r['offer_id'], 'offer_name' ),
			'flow'			=> $r['flow_id'],
			'site'			=> $r['site_id'],
			'site_url'		=> $core->wmsale->get( 'site', $r['site_id'], 'site_url' ),
			'space'			=> $r['space_id'],
			'space_url'		=> $core->wmsale->get( 'site', $r['space_id'], 'site_url' ),
			'ip'			=> int2ip( $r['order_ip'] ),
			'utm_id'		=> $r['utm_id'],
			'utm_content'	=> $r['utm_cn'],
			'utm_source'	=> $r['utm_src'],
		);
	} unset ( $r, $order );

	return $result;

}


// Statistics
function api_wm_sources ( $core, $user ) {

	$today = date( 'Ymd' );
	$week1 = date( 'Ymd', strtotime( '-2 week' ) );

	extract(params( $core, array( 'to' => 'date', 'from' => 'date', 'offer', 'flow', 'cutoff', 'network', 'group', 'filter' ) ));
	if ( ! $to || $to > $today ) $to = $today;
	if ( $from > $to ) $from = $to;
	if ( ! $from ) $from = $week1;
	if ( ! $cutoff ) $cutoff = 10;

	require_once PATH . 'lib/webmaster.php';
	list ( $offer, $flow, $stats ) = webmaster_sources ( $core, $user, $from, $to, $offer, $flow, $group, $network, $cutoff, 1, $filter );

	return $stats;

}

//
// Sale API
//

// List orders
function api_sale_list ( $core, $user ) {

	$cid = $core->user->get( $user, 'user_comp' );
	if ( ! $cid ) return array( 'status' => 'error', 'error' => 'access-denied' );
	$where = array( "comp_id = '$cid'" );

	// Order status
	if ( $s = ( $core->post['status'] ? (int) $core->post['status'] : (int) $core->get['status'] ) ) {		if ( $s < 0 ) {			switch ( $s ) {
				case -1:	$where[] = "order_status NOT IN ( 5, 12 )"; break;
				case -2:	$where[] = "order_status < 5"; break;
				case -3:	$where[] = "order_status > 5 AND order_status < 12"; break;
			}
		} else $where[] = "order_status = '$s'";
	}

	// Timing
	$f = $core->post['from'] ? (int) $core->post['from'] : (int) $core->get['from'];
	$t = $core->post['to'] ? (int) $core->post['to'] : (int) $core->get['to'];
	if (!( $f && $t )) {
		if ( $f ) $where[] = "order_time > '$f'";
		if ( $f ) $where[] = "order_time < '$t'";
	} else $where[] = "order_time BETWEEN '$f' AND '$t'";

	// Order IDs
	if ( $id = $core->post['oid'] ? $core->post['oid'] : $core->get['oid'] ) {
		$ids = explode( ',', $id );
	} else $ids = $core->post['ids'] ? $core->post['ids'] : $core->get['ids'];
	if ( $ids ) {
		$ids = array_map( 'intval', $ids );
		if ( count( $ids ) > 1 ) {
			$where[] = 'order_id IN ( '.implode( ', ', $ids ).' )';
		} elseif ( $ids ) $where[] = "order_id = '".$ids[0]."'";
	}

	// External Order IDs
	if ( $eid = $core->post['eid'] ? $core->post['eid'] : $core->get['eid'] ) {
		$eids = explode( ',', $eid );
	} else $eids = $core->post['eids'] ? $core->post['eids'] : $core->get['eids'];
	if ( $eids ) {
		$eids = array_map( 'intval', $eids );
		if ( count( $eids ) > 1 ) {
			$where[] = 'ext_oid IN ( '.implode( ', ', $eids ).' )';
		} elseif ( $eids ) $where[] = "ext_oid = '".$eids[0]."'";
	}

	// Other IDs
	if ( $o = ( $core->post['offer'] ? (int) $core->post['offer'] : (int) $core->get['offer'] ) ) $where[] = "offer_id = '$o'";
	if ( $o = ( $core->post['after'] ? (int) $core->post['after'] : (int) $core->get['after'] ) ) $where[] = "order_id > '$s'";

	// Get items into array
	$items = array();
	$where = implode( ' AND ', $where );
	$query = $core->db->start( "SELECT * FROM ".DB_ORDER." WHERE $where" );
	while ( $o = $core->db->one( $query ) ) {		$items[] = array(
			'id'		=> (int) $o['order_id'],
			'ext'		=> (int) $o['ext_oid'],
			'offer'		=> (int) $o['offer_id'],
			'wm'		=> (int) $o['wm_id'],
			'status'	=> (int) $o['order_status'],
			'reason'	=> (int) $o['order_reason'],
			'check'		=> (int) $o['order_check'],
			'calls'		=> (int) $o['order_calls'],
			'site'		=> $o['site_id'] ? $core->wmsale->get( 'site', $o['site_id'], 'site_url' ) : false,
			'ip'		=> int2ip( $o['order_ip'] ),
			'time'		=> (int) $o['order_time'],
			'name'		=> $o['order_name'],
			'gender'	=> (int) $o['order_gender'],
			'phone'		=> $o['order_phone'],
			'country'	=> $o['order_country'],
			'index'		=> $o['order_index'],
			'addr'		=> $o['order_addr'],
			'area'		=> $o['order_area'],
			'city'		=> $o['order_city'],
			'street'	=> $o['order_street'],
			'count'		=> (int) $o['order_count'],
			'items'		=> $o['order_items'] ? unserialize( $o['order_items'] ) : false,
			'delivery'	=> (int) $o['order_delivery'],
			'discount'	=> (int) $o['order_discount'],
			'more'		=> (int) $o['order_more'],
			'price'		=> (int) $o['order_price'],
			'comment'	=> $o['order_comment'],
		);
	} $core->db->stop( $query );

	return $items;

}

// Edit order data
function api_sale_edit ( $core, $user ) {

	// Get order data
	$cid = $core->user->get( $user, 'user_comp' );
	if ( ! $cid ) return array( 'status' => 'error', 'error' => 'access-denied' );
	$id = $core->post['oid'] ? (int) $core->post['oid'] : (int) $core->get['oid'];
	$eid = $core->post['eid'] ? (int) $core->post['eid'] : (int) $core->get['eid'];
	if (!( $id || $eid )) return array( 'status' => 'error', 'error' => 'orderid' );
	$order = $id ? $core->db->row( "SELECT * FROM ".DB_ORDER." WHERE comp_id = '$cid' AND order_id = '$id' LIMIT 1" ) : $core->db->row( "SELECT * FROM ".DB_ORDER." WHERE comp_id = '$cid' AND ext_oid = '$eid' LIMIT 1" );
	if (! $order['order_id'] ) return array( 'status' => 'error', 'error' => 'access' );

	$data = array();
	// Get parameters
	if (isset( $core->get['accept'] )) $data['accept'] = (int) $core->get['accept'];
	if (isset( $core->get['status'] )) $data['status'] = (int) $core->get['status'];
	if (isset( $core->get['reason'] )) $data['reason'] = (int) $core->get['reason'];
	if (isset( $core->get['check'] )) $data['check'] = (int) $core->get['check'];
	if (isset( $core->get['track'] )) $data['track'] = $core->get['track'];
	if (isset( $core->get['calls'] )) $data['calls'] = $core->get['calls'];
	// Post parameters same to get
	if (isset( $core->post['accept'] )) $data['accept'] = (int) $core->post['accept'];
	if (isset( $core->post['status'] )) $data['status'] = (int) $core->post['status'];
	if (isset( $core->post['reason'] )) $data['reason'] = (int) $core->post['reason'];
	if (isset( $core->post['check'] )) $data['check'] = (int) $core->post['check'];
	if (isset( $core->post['track'] )) $data['track'] = $core->post['track'];
	if (isset( $core->post['calls'] )) $data['calls'] = $core->post['calls'];
	// Post only parameters
	if (isset( $core->post['name'] )) $data['name'] = $core->post['name'];
	if (isset( $core->post['phone'] )) $data['phone'] = $core->post['phone'];
	if (isset( $core->post['addr'] )) $data['addr'] = $core->post['addr'];
	if (isset( $core->post['index'] )) $data['index'] = $core->post['index'];
	if (isset( $core->post['area'] )) $data['area'] = $core->post['area'];
	if (isset( $core->post['city'] )) $data['city'] = $core->post['city'];
	if (isset( $core->post['street'] )) $data['street'] = $core->post['street'];
	if (isset( $core->post['delivery'] )) $data['delivery'] = (int) $core->post['delivery'];
	if (isset( $core->post['discount'] )) $data['discount'] = (int) $core->post['discount'];
	if (isset( $core->post['count'] )) $data['count'] = (int) $core->post['count'];
	if (isset( $core->post['items'] )) $data['items'] = $core->post['items'];
	if (isset( $core->post['more'] )) $data['more'] = (int) $core->post['more'];
	if (isset( $core->post['comment'] )) $data['comment'] = (int) $core->post['comment'];

	// Save the order
	require_once PATH_LIB . 'common.php';
	return order_edit( $core, $order['order_id'], $data, $order ) ? array( 'status' => 'ok' ) : array( 'status' => 'error', 'error' => 'edit' );

}