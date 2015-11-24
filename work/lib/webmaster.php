<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / webmaster.php
 *  Description:	WebMaster interface
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

//
// Generic functions
//

// Adding new flow
function webmaster_flow_add ( $core, $user, $id, $name = '' ) {
	$offer = $core->wmsale->get( 'offer', $id );
	$lands = $core->wmsale->get( 'lands', $id );
	if ( $offer['offer_active'] ) {
       	if ( $core->db->add( DB_FLOW, array( 'user_id' => $user, 'offer_id' => $id, 'flow_name' => $name, 'flow_site' => $lands[0]['site_id'] ) ) ) {
			$oid = $core->db->lastid();
			if ( ! $name ) $core->db->edit( DB_FLOW, array( 'flow_name' => sprintf( "%s - %s", $offer['offer_name'], $oid ) ), "flow_id = '$oid'" );
			$core->wmsale->clear( 'flows', $user );
			return $oid;
       	} else return false;
	} else return -1;
}

// Edit flow data
function webmaster_flow_edit ( $core, $user, $id, $data ) {

	$edit = array();
	if (isset( $data['name'] ))		$edit['flow_name'] 	= $data['name'];
	if (isset( $data['site'] ))		$edit['flow_site'] 	= $data['site'];
	if (isset( $data['space'] ))	$edit['flow_space'] = $data['space'];
	if (isset( $data['cb'] ))		$edit['flow_cb'] 	= $data['cb'];
	if (isset( $data['param'] ))	$edit['flow_param'] = $data['param'];
	if (isset( $data['url'] ))		$edit['flow_url'] 	= $data['url'];
	if (isset( $data['pbu'] ))		$edit['flow_pbu'] 	= $data['pbu'];

	$fuser = $core->db->field( "SELECT user_id FROM ".DB_FLOW." WHERE flow_id = '$id' LIMIT 1" );
	if ( $user == $fuser ) {
		if ( $core->db->edit( DB_FLOW, $edit, "flow_id = '$id'" ) ) {
			$core->wmsale->clear( 'flow', $id );
			$core->wmsale->clear( 'flows', $user );
			return 1;
		} else return 0;
	} else return -1;

}

// Delete flow
function webmaster_flow_del ( $core, $user, $id ) {

	$fuser = $core->db->field( "SELECT user_id FROM ".DB_FLOW." WHERE flow_id = '$id' LIMIT 1" );
	if ( $user == $fuser ) {
		if ( $core->db->del( DB_FLOW, "flow_id = '$id'" ) ) {
			$core->db->edit( DB_ORDER, array( 'flow_id' => 0 ), "flow_id = '$id'" );
			$core->db->edit( DB_CLICK, array( 'flow_id' => 0 ), "flow_id = '$id'" );
			$core->db->del( DB_STATS, "flow_id = '$id'" );
			$core->wmsale->clear( 'flow', $id );
			$core->wmsale->clear( 'flows', $user );
			return 1;
		} else return 0;
	} else return -1;

}

// Clicks Statistics
function webmaster_clicks ( $core, $user, $from, $to, $f, $o ) {
	$today = date( 'Ymd' );
	$week1 = date( 'Ymd', strtotime( '-1 week' ) );
	$vip = $core->user->get( $user, 'user_vip' );

	$oids = $core->db->col( "SELECT DISTINCT offer_id FROM ".DB_FLOW." WHERE user_id = '$user'" );
	$oids = implode( ',', $oids );
	$price = array();
	$offer = $oids ? $core->db->icol( "SELECT offer_id, offer_name FROM ".DB_OFFER." WHERE offer_id IN ( $oids ) ORDER BY offer_name ASC" ) : array();
	$flow = $core->db->icol( "SELECT flow_id, flow_name FROM ".DB_FLOW." WHERE user_id = '".$user."' ".( $o ? " AND offer_id = '$o' " : '' )." ORDER BY flow_name ASC" );
	$flows = $f ? $f : implode( ', ', array_keys( $flow ) );

	$stats = array();
	if ( $flows ) {

		// Today statistics of clicks
		if ( $to == $today ) {
			$stats[$today] = $core->db->row( "SELECT COUNT(*) AS `clicks`, SUM(click_unique) AS `unique` FROM ".DB_CLICK." WHERE flow_id IN ( $flows ) AND click_date = '$today' AND click_space = 0" );
			$stats[$today]['spaces'] = $core->db->field( "SELECT COUNT(*) FROM ".DB_CLICK." WHERE flow_id IN ( $flows ) AND click_date = '$today' AND click_space = 1" );
			$stats[$today]['suni'] = $core->db->field( "SELECT COUNT(*) FROM ".DB_CLICK." WHERE flow_id IN ( $flows ) AND click_date = '$today' AND click_space = 1 AND click_unique = 1" );
           }

		// Earlier statistics of clicks
		if ( $from < $today ) {
        	$sts = $core->db->data( "SELECT stat_date, SUM(stat_space) AS `spaces`, SUM(stat_suni) AS `suni`, SUM(stat_click) AS `clicks`, SUM(stat_unique) AS `unique`, SUM(count_accept) AS `ca`, SUM(count_cancel) AS `cc`, SUM(sum_accept) AS `sa`, SUM(sum_cancel) AS `sc` FROM ".DB_STATS." WHERE flow_id IN ( $flows ) AND stat_date BETWEEN '$from' AND '$to' GROUP BY stat_date" );
        	foreach ( $sts as $s ) $stats[$s['stat_date']] = $s;
			unset ( $sts, $s );
		}

		// Order count statistics
		if ( ( $from > $week1 || $to > $week1 ) && $from < $today ) {

			$ff = strtotime( date2form( max( $week1, $from ) ) . ' 00:00:00' );
			$tt = strtotime( date2form( $to ) . ' 23:59:59' );

			$orders = $core->db->start( "SELECT order_id, order_webstat, order_time, offer_id FROM ".DB_ORDER." WHERE flow_id IN ( $flows ) AND order_time BETWEEN '$ff' AND '$tt'" );
			while ( $oo = $core->db->one( $orders ) ) {

            	if ( $oo['order_webstat'] < 5 ) {
            		$l = 'w';
            	} elseif ( $oo['order_webstat'] > 5 && $oo['order_webstat'] < 11 ) {
            		$l = 'a';
            	} else $l = 'c';
				$d = date( 'Ymd', $oo['order_time'] );

				$price = $core->wmsale->price( $oo['offer_id'], $user, 'wmp' );
				if ( $stats[$d]['c'.$l] ) $stats[$d]['c'.$l] += 1; else $stats[$d]['c'.$l] = 1;
				if ( $stats[$d]['s'.$l] ) $stats[$d]['s'.$l] += $price; else $stats[$d]['s'.$l] = $price;

			} $core->db->stop( $orders );

		}

		krsort( $stats );
		foreach ( $stats as $d => &$s ) $s['stat_date'] = $d;

	}

	return array( $offer, $flow, $stats );

}

// Source Statistics
function webmaster_sources ( $core, $user, $from, $to, $o, $f, $g, $q, $c, $all, $fi ) {

	$oids = $core->db->col( "SELECT DISTINCT offer_id FROM ".DB_FLOW." WHERE user_id = '$user'" );
	$oids = implode( ',', $oids );
	$offer = $oids ? $core->db->icol( "SELECT offer_id, offer_name FROM ".DB_OFFER." WHERE offer_id IN ( $oids ) ORDER BY offer_name ASC" ) : array();
	$flow = $core->db->icol( "SELECT flow_id, flow_name FROM ".DB_FLOW." WHERE user_id = '$user' ".( $o ? " AND offer_id = '$o' " : '' )." ORDER BY flow_name ASC" );
	$flows = $f ? $f : implode( ', ', array_keys( $flow ) );
	$flows = " AND flow_id IN ( $flows ) ";
	if ( $all ) $flows = '';

	$stats = array();
	$gv = $g ? 'utm_src' : 'utm_cn';

	if ( $fi ) {
		$su = "utm_id = '$q' AND ".( $g ? 'utm_cn' : 'utm_src' )." = '$fi'";
	} else $su = $q ? "utm_id = '$q'" : "utm_id > '0'";

	// Counting clicks
	$spaces = $core->db->data( "SELECT COUNT(*) AS `c`, utm_id, $gv FROM ".DB_CLICK." WHERE ( click_date BETWEEN '$from' AND '$to' ) $flows AND $su AND click_space = 1 AND $gv > '0' GROUP BY utm_id, $gv" );
	foreach ( $spaces as $s ) $stats[ $s['utm_id'] . ':' . $s[$gv] ]['spaces'] = $s['c'];
	unset ( $spaces );
	$suni = $core->db->data( "SELECT COUNT(*) AS `c`, utm_id, $gv FROM ".DB_CLICK." WHERE ( click_date BETWEEN '$from' AND '$to' ) $flows AND $su AND click_space = 1 AND click_unique = 1 AND $gv > '0' GROUP BY utm_id, $gv" );
	foreach ( $suni as $s ) $stats[ $s['utm_id'] . ':' . $s[$gv] ]['suni'] = $s['c'];
	unset ( $suni );
	$clicks = $core->db->data( "SELECT COUNT(*) AS `c`, utm_id, $gv FROM ".DB_CLICK." WHERE ( click_date BETWEEN '$from' AND '$to' ) $flows AND $su AND click_space = 0 AND $gv > '0' GROUP BY utm_id, $gv" );
	foreach ( $clicks as $s ) $stats[ $s['utm_id'] . ':' . $s[$gv] ]['clicks'] = $s['c'];
	unset ( $clicks );
	$unique = $core->db->data( "SELECT COUNT(*) AS `c`, utm_id, $gv FROM ".DB_CLICK." WHERE ( click_date BETWEEN '$from' AND '$to' ) $flows AND $su AND click_space = 0 AND click_unique = 1 AND $gv > '0' GROUP BY utm_id, $gv" );
	foreach ( $unique as $s ) $stats[ $s['utm_id'] . ':' . $s[$gv] ]['unique'] = $s['c'];
	unset ( $unique );

	// CutOff
	foreach ( $stats as $d => $s ) {
		$tc = max( (int) $s['spaces'], (int) $s['clicks'], (int) $s['unique'] );
		if ( $tc < $c ) unset ( $stats[$d] );
	}

	// Counting orders
	$ff = strtotime( date2form( $from ) . ' 00:00:00' );
	$tt = strtotime( date2form( $to ) . ' 23:59:59' );
	$orders = $core->db->start( "SELECT order_webstat, utm_id, $gv FROM ".DB_ORDER." WHERE $su AND $gv > '0' $flows AND order_time BETWEEN '$ff' AND '$tt'" );
	while ( $oo = $core->db->one( $orders ) ) {
		$d = $oo['utm_id'] . ':' . $oo[$gv];
		if ( ! $stats[$d] ) continue;
          	if ( $oo['order_webstat'] < 5 ) {
          		$l = 'w';
          	} elseif ( $oo['order_webstat'] > 5 && $oo['order_webstat'] < 11 ) {
          		$l = 'a';
          	} else $l = 'c';
		if ( $stats[$d]['c'.$l] ) $stats[$d]['c'.$l] += 1; else $stats[$d]['c'.$l] = 1;
		if ( $stats[$d]['ct'] ) $stats[$d]['ct'] += 1; else $stats[$d]['ct'] = 1;
	} $core->db->stop( $orders );

	foreach ( $stats as $d => &$s ) list( $s['network'], $s['source'] ) = explode( ':', $d );

	return array( $offer, $flow, $stats );

}

// Target Statistics
function webmaster_target ( $core, $user, $from, $to, $o, $f ) {

	// Flows and orders
	$oids = $core->db->col( "SELECT DISTINCT offer_id FROM ".DB_FLOW." WHERE user_id = '$user'" );
	if ( ! $oids ) return array( array(), array(), array() );
	$oids = implode( ',', $oids );
	$offer = $oids ? $core->db->icol( "SELECT offer_id, offer_name FROM ".DB_OFFER." WHERE offer_id IN ( $oids ) ORDER BY offer_name ASC" ) : array();
	$flow = $core->db->icol( "SELECT flow_id, flow_name FROM ".DB_FLOW." WHERE user_id = '$user' ".( $o ? " AND offer_id = '$o' " : '' )." ORDER BY flow_name ASC" );
	$flows = $f ? " flow_id = '$f' " : '';

	// Targets
	$target = $core->wmsale->get( 'targets', $user );
	if ( $target ) {
		$tids = array();
		foreach ( $target as $t ) $tids[] = $t['target_id'];
		$tids = implode( ', ', $tids );
		$tids = " AND target_id IN ( $tids ) ";
	} else return array( $offer, $flow, array() );

	// Counting clicks
	$spaces = $core->db->icol( "SELECT target_id, COUNT(*) FROM ".DB_CLICK." WHERE ( click_date BETWEEN '$from' AND '$to' ) $flows $tids AND click_space = 1 GROUP BY target_id" );
	$suni = $core->db->icol( "SELECT target_id, COUNT(*) FROM ".DB_CLICK." WHERE ( click_date BETWEEN '$from' AND '$to' ) $flows $tids AND click_space = 1 AND click_unique = 1 GROUP BY target_id" );
	$clicks = $core->db->icol( "SELECT target_id, COUNT(*) FROM ".DB_CLICK." WHERE ( click_date BETWEEN '$from' AND '$to' ) $flows $tids AND click_space = 0 GROUP BY target_id" );
	$unique = $core->db->icol( "SELECT target_id, COUNT(*) FROM ".DB_CLICK." WHERE ( click_date BETWEEN '$from' AND '$to' ) $flows $tids AND click_space = 0 AND click_unique = 1 GROUP BY target_id" );

	// Processing
	$stats = array();
	foreach ( $target as $t ) {
		$i = $t['target_id'];
		$stats[$i] = array(
			'id' => $i, 'name' => $t['target_name'], 'type' => $t['target_type'],
			'space' => $spaces[$i], 'suni'  => $suni[$i],
			'clicks' => $clicks[$i], 'unique'  => $unique[$i]
		);
	}

	// Counting orders
	$ff = strtotime( date2form( $from ) . ' 00:00:00' );
	$tt = strtotime( date2form( $to ) . ' 23:59:59' );
	$orders = $core->db->start( "SELECT order_webstat, target_id FROM ".DB_ORDER." WHERE order_time BETWEEN '$ff' AND '$tt' $flows $tids" );
	while ( $oo = $core->db->one( $orders ) ) {
		if ( $oo['order_webstat'] < 5 ) {
			$l = 'w';
		} elseif ( $oo['order_webstat'] > 5 && $oo['order_webstat'] < 11 ) {
			$l = 'a';
		} else $l = 'c';
		if ( $stats[$oo['target_id']]['c'.$l] ) $stats[$oo['target_id']]['c'.$l] += 1; else $stats[$oo['target_id']]['c'.$l] = 1;
		if ( $stats[$oo['target_id']]['ct'] ) $stats[$oo['target_id']]['ct'] += 1; else $stats[$oo['target_id']]['ct'] = 1;
	} $core->db->stop( $orders );

	return array( $offer, $flow, $stats );

}

// FlowStats Statistics
function webmaster_flowstat ( $core, $user, $from, $to ) {

	$offer = $core->wmsale->get( 'offers' );
	$flow = $core->wmsale->get( 'flows', $user );
	$flows = implode( ', ', array_keys( $flow ) );
	if ( ! $flow ) return false;

	// Counting clicks
	$spaces = $core->db->icol( "SELECT flow_id, COUNT(*) FROM ".DB_CLICK." WHERE ( click_date BETWEEN '$from' AND '$to' ) AND flow_id IN ( $flows ) AND click_space = 1 GROUP BY flow_id" );
	$suni = $core->db->icol( "SELECT flow_id, COUNT(*) FROM ".DB_CLICK." WHERE ( click_date BETWEEN '$from' AND '$to' ) AND flow_id IN ( $flows ) AND click_space = 1 AND click_unique = 1 GROUP BY flow_id" );
	$clicks = $core->db->icol( "SELECT flow_id, COUNT(*) FROM ".DB_CLICK." WHERE ( click_date BETWEEN '$from' AND '$to' ) AND flow_id IN ( $flows ) AND click_space = 0 GROUP BY flow_id" );
	$unique = $core->db->icol( "SELECT flow_id, COUNT(*) FROM ".DB_CLICK." WHERE ( click_date BETWEEN '$from' AND '$to' ) AND flow_id IN ( $flows ) AND click_space = 0 AND click_unique = 1 GROUP BY flow_id" );

	// Processing
	$stats = array();
	foreach ( $flow as $i => $f ) {
		$oid = $core->wmsale->get( 'flow', $i, 'offer_id' );
		$stats[$i] = array(
			'id' => $i, 'flow' => $f, 'offer' => $offer[$oid], 'oid' => $oid,
			'space' => $spaces[$i], 'suni'  => $suni[$i],
			'clicks' => $clicks[$i], 'unique'  => $unique[$i]
		);
	}

	// Counting orders
	$ff = strtotime( date2form( $from ) . ' 00:00:00' );
	$tt = strtotime( date2form( $to ) . ' 23:59:59' );
	$orders = $core->db->start( "SELECT order_webstat, flow_id FROM ".DB_ORDER." WHERE order_time BETWEEN '$ff' AND '$tt' AND flow_id IN ( $flows )" );
	while ( $oo = $core->db->one( $orders ) ) {
		if ( $oo['order_webstat'] < 5 ) {
			$l = 'w';
		} elseif ( $oo['order_webstat'] > 5 && $oo['order_webstat'] < 11 ) {
			$l = 'a';
		} else $l = 'c';
		if ( $stats[$oo['flow_id']]['c'.$l] ) $stats[$oo['flow_id']]['c'.$l] += 1; else $stats[$oo['flow_id']]['c'.$l] = 1;
		if ( $stats[$oo['flow_id']]['ct'] ) $stats[$oo['flow_id']]['ct'] += 1; else $stats[$oo['flow_id']]['ct'] = 1;
	} $core->db->stop( $orders );

	return $stats;

}

//
// Module functions
//

function webmaster_menu ( $core, $menu ) {

	array_push( $menu, 'flow', 'offers' );
	$menu['stats'] = array( 'stats', 'lead', 'flowstat', 'target', 'sources', 'bl' );
	array_push( $menu, 'referal' );
	return $menu;

}

function webmaster_action ( $core ) {
	$action = ( $core->get['a'] ) ? $core->get['a'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );

	switch ( $action ) {

	  case 'flow-add':

	  	$oid = webmaster_flow_add ( $core, $core->user->id, $id );
	  	if ( $oid ) {        	if ( $oid > 0 ) {	            $core->go($core->url( 'im', 'flow', $oid, 'ok' ));
        	} else $core->go($core->url( 'mm', 'offers', 'inactive' ));
	  	} else $core->go($core->url( 'mm', 'offers', 'error' ));

	  case 'flow-edit':

		$data = array(
			'name'	=> $core->text->line( $core->post['name'] ),
			'site'	=> (int) $core->post['site'],
			'space'	=> (int) $core->post['space'],
			'cb'	=> $core->post['cb'] ? 1 : 0,
			'param'	=> $core->post['param'] ? 1 : 0,
			'url'	=> $core->text->url( $core->post['url'] ),
			'pbu'	=> $core->text->url( $core->post['pbu'] ),
		);

		$result = webmaster_flow_edit( $core, $core->user->id, $id, $data );
	  	if ( $result ) {
        	if ( $result > 0 ) {
	            $core->go($core->url( 'mm', 'flow', 'save' ));
        	} else $core->go($core->url( 'mm', '', 'access' ));
	  	} else $core->go($core->url( 'mm', 'flow', 'error' ));

	  case 'flow-ajax':

		$data = array();
		if (isset( $core->get['site'] ))	$data['site'] 	= (int) $core->get['site'];
		if (isset( $core->get['space'] )) 	$data['space'] 	= (int) $core->get['space'];
		if (isset( $core->get['cb'] )) 		$data['cb'] 	= $core->get['cb'] ? 1 : 0;
		if (isset( $core->get['param'] )) 	$data['param'] 	= $core->get['param'] ? 1 : 0;
		if (isset( $core->get['url'] ))		$data['url'] 	= $core->text->url( $core->get['url'] );
		if (isset( $core->get['pbu'] ))		$data['pbu'] 	= $core->text->url( $core->get['pbu'] );
		$result = webmaster_flow_edit( $core, $core->user->id, $id, $data );
	  	echo ( $result > 0 ) ? 'ok' : error;
	  	$core->_die();

	  case 'flow-del':

		$result = webmaster_flow_del( $core, $core->user->id, $id );
	  	if ( $result ) {
        	if ( $result > 0 ) {
	            $core->go($core->url( 'mm', 'flow', 'del' ));
        	} else $core->go($core->url( 'mm', '', 'access' ));
	  	} else $core->go($core->url( 'mm', 'flow', 'error' ));

	  case 'flow-target':

		$target = $core->wmsale->get( 'target', $core->user->id );
	  	$result = '<td class="olt-label">Цель</td><td class="olt-field"><select id="offer'.$id.'targt" onchange="makelink('.$id.');"><option value="0">&mdash; нет цели &mdash; </option>';
	  	foreach ( $target as $v => $n ) $result .= '<option value="'.$v.'">'.$n.'</option>';
		$result .= '</select></td>';
		echo $result;
		$core->_die();

	  //
	  // Black list
	  //

	  case 'bl-add':

		$u = (int) $core->get['u'];
		$i = preg_replace ("#([^a-z0-9\-\_\.]*)#si", '', strtolower( $core->get['i'] ));
		$t = (int) $core->get['t'];

		$id = $core->db->field( "SELECT bl_id FROM ".DB_BL." WHERE user_id = '".$core->user->id."' AND bl_utm = '$u' AND bl_type = '$t' AND bl_item = '$i' LIMIT 1" );
		if ( ! $id ) {			$core->db->query( "INSERT INTO ".DB_BL." SET user_id = '".$core->user->id."', bl_utm = '$u', bl_type = '$t', bl_item = '$i', bl_time = '".time()."'" );
			$id = $core->db->lastid();
		}

		$ajax = ( $core->get['z'] == 'ajax' ) ? true : false;
		if ( $ajax ) {
           	echo(json_encode(array( 'status' => 'ok', 'id' => $t.'_'.$u.'_'.strtr( $i, '.', '_' ), 'newid' => $id, 'cls' => 'decline', 'url' => $core->url( 'a', 'bl-del', $id ).'?', 'text' => $core->lang['bl_del'] )));
           	$core->_die();
		} else msgo( $core, 'ok' );

	  case 'bl-del':

		$bl = $core->db->row( "SELECT * FROM ".DB_BL." WHERE bl_id = '$id' LIMIT 1" );
		$ajax = ( $core->get['z'] == 'ajax' ) ? true : false;
		if ( $bl['user_id'] = $core->user->id ) {
			$core->db->query( "DELETE FROM ".DB_BL." WHERE bl_id = '$id' LIMIT 1" ); 			if ( $ajax ) {
            	echo(json_encode(array( 'status' => 'ok', 'id' => $id, 'newid' => $bl['bl_type'].'_'.$bl['bl_utm'].'_'.strtr( $bl['bl_item'], '.', '_' ), 'cls' => 'accept', 'url' => $core->url( 'a', 'bl-add', 0 ) . '?i='.$bl['bl_item'].'&u='.$bl['bl_utm'].'&t='.$bl['bl_type'], 'text' => $core->lang['bl_add'] )));
			} else msgo( $core, 'ok' );
		} else {			if ( $ajax ) {            	echo(json_encode(array( 'status' => 'error', 'id' => $id )));
			} else msgo( $core, 'error' );
		} $core->_die();

	  case 'bl-load':

		if ( $u = (int) $core->get['u'] ) {			$name = ( $id ? 'sites-' : 'teasers-' ) . strtolower( $core->lang['stat_srcs'][$u] );        	$items = $core->db->col( "SELECT bl_item FROM ".DB_BL." WHERE bl_utm = '$u' AND bl_type = '$id' ORDER BY bl_item ASC" );
            $blacklist = implode( "\r\n", $items );
		} else {        	$name = ( $id ? 'sites' : 'teasers' );
        	$itsl = array(); $blacklist = '';
        	$items = $core->db->icol( "SELECT bl_item, bl_utm FROM ".DB_BL." WHERE bl_type = '$id' ORDER BY bl_item ASC" );
        	foreach ( $items as $i => $v ) $itsl[$v][] = $i; unset ( $items, $i, $v );
			foreach ( $itsl as $i => $v ) $blacklist .= $core->lang['stat_srcs'][$i] . "\r\n" . implode( "\r\n", $v ) . "\r\n\r\n";
		}

		header( 'Content-type: text/plain; charset=utf-8' );
		header( "Content-Disposition: attachment; filename=blacklist-$name.txt" );
		echo $blacklist;
	  	$core->_die();

	  //
	  // Domains
	  //

	  // New parked domain
	  case 'dmn-add':

		$url = $core->text->link($core->post['url']);
		$core->db->add( DB_DOMAIN, array( 'user_id' => $core->user->id, 'dom_url' => $url ) );
		$core->wmsale->clear( 'domain', $core->user->id );
  		$core->go($core->url( 'mm', 'domain', 'ok' ));

	  // Delete parked domain
	  case 'dmn-del':

		$dd = $core->db->field( "SELECT user_id FROM ".DB_DOMAIN." WHERE dom_id = '$id' LIMIT 1" );
		if ( $dd == $core->user->id ) {
			$core->db->del( DB_DOMAIN, "dom_id = '$id'" );
			$core->wmsale->clear( 'domain', $core->user->id );
	  		$core->go($core->url( 'mm', 'domain', 'del' ));
	  	} else $core->go($core->url( 'mm', 'domain', 'access' ));

	  // Check domain for working
	  case 'dmn-check':

	  	$dom = $core->db->field( "SELECT dom_url FROM ".DB_DOMAIN." WHERE dom_id = '$id' LIMIT 1" );
	  	$data = @file_get_contents( 'http://' . $dom . '/ok' );
	  	if ( $data == 'ok' ) {
	  		$core->go($core->url( 'mm', 'domain', 'check' ));
	  	} else $core->go($core->url( 'mm', 'domain', 'error' ));

	  //
	  // Targets
	  //

	  // Adding new target
	  case 'target-add':

		$name = $core->text->line( $core->post['name'] );
		$type = (int) $core->post['type'];
		if ( $name ) $core->db->add( DB_TARGET, array( 'target_name' => $name, 'target_type' => $type, 'user_id' => $core->user->id ) );
		$core->wmsale->clear( 'target', $core->user->id );
		$core->wmsale->clear( 'targets', $core->user->id );
	  	$core->go($core->url( 'mm', 'target', 'ok' ));

	  // Edit target name and type
	  case 'target-edit':

		$targets = $core->wmsale->get( 'target', $core->user->id );
		if ( $targets[$id] ) {
			$name = $core->text->line( $core->post['name'] );
			$type = (int) $core->post['type'];
			if ( $name ) $core->db->edit( DB_TARGET, array( 'target_name' => $name, 'target_type' => $type ), "target_id = '$id'" );
			$core->wmsale->clear( 'target', $core->user->id );
			$core->wmsale->clear( 'targets', $core->user->id );
		  	$core->go($core->url( 'mm', 'target', 'ok' ));
		} else $core->go($core->url( 'mm', 'target', 'access' ));

	  // Delete target info
	  case 'target-del':

		$targets = $core->wmsale->get( 'target', $core->user->id );
		if ( $targets[$id] ) {
			$core->db->edit( DB_ORDER, array( 'target_id' => 0 ), "target_id = '$id'" );
			$core->db->edit( DB_CLICK, array( 'target_id' => 0 ), "target_id = '$id'" );
			$core->db->del( DB_TARGET, "target_id = '$id'" );
			$core->wmsale->clear( 'target', $core->user->id );
			$core->wmsale->clear( 'targets', $core->user->id );
	  		$core->go($core->url( 'mm', 'target', 'del' ));
	  	} else $core->go($core->url( 'mm', 'target', 'access' ));

	}

	return false;

}

function webmaster_module ( $core ) {
	$module	= ( $core->get['m'] ) ? $core->get['m'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );
	$page	= ( $core->get['page'] > 0 ) ? (int) $core->get['page'] : 1;
	$message = ( $core->get['message'] ) ? $core->get['message'] : null;

	switch ( $module ) {

	  case 'offers':

		require_once PATH_LIB . 'offers.php';
		offers ( $core );

	  case 'referal':

	    $sh = 30; $st = $sh * ( $page - 1 );
    	$users	= $core->db->field ( "SELECT COUNT(*) FROM ".DB_USER." WHERE user_ref = '".$core->user->id."'");
    	$user	= $users ? $core->db->data ( "SELECT * FROM ".DB_USER." WHERE user_ref = '".$core->user->id."' ORDER BY user_name ASC LIMIT $st, $sh" ) : array();

	    $core->mainline->add ( $core->lang['referal_h'], $core->url( 'm', 'referal' ) );
	    $core->header ();

	    $core->tpl->load( 'body', 'referal' );

	    $core->tpl->vars ('body', array (
	        'title'		    => $core->lang['referal_h'],
            'text'			=> $core->text->lines( sprintf( $core->lang['referal_t'], $core->user->id ) ),
	        'nousers'		=> $core->lang['referal_no'],
	        'name'  	    => $core->lang['user'],
            'pages'			=> pages ( $core->url('m', 'referal'), $users, $sh, $page ),
            'shown'			=> $users ? sprintf( $core->lang['shown'], $st+1, min( $st+$sh, $users ), $users ) : '',
	    ));

	    if (count( $user )) foreach ( $user as &$i ) {
	        $core->tpl->block ('body', 'user', array (
	            'name'		=> $i['user_name'],
	            'cash'      => rur( $i['user_got'] ),
	            'flwa'		=> (int) $i['user_flwa'],
	        ));
	    } else $core->tpl->block( 'body', 'nouser' );

		$core->tpl->output ('body');

		$core->footer();
		$core->_die();

	  case 'lead':

		$where = array( "wm_id = '".$core->user->id."'" );

		if ( isset( $core->get['d'] ) && $core->get['d'] ) {
			$d = date2form(form2date( $core->get['d'] ));
			$ds = strtotime( $d . ' 00:00:00' );
			$de = strtotime( $d . ' 23:59:59' );
			$where[] = " order_time BETWEEN '$ds' AND '$de' ";
		} else $d = false;

		if ( isset( $core->get['o'] ) && $core->get['o'] ) {
			$o = (int) $core->get['o'];
			$where[] = " offer_id = '$o' ";
		} else $o = false;

		if ( isset( $core->get['f'] ) && $core->get['f'] ) {
			$f = (int) $core->get['f'];
			$where[] = " flow_id = '$f' ";
		} else $f = false;

		if ( isset( $core->get['w'] ) && $core->get['w'] ) {
			$w = (int) $core->get['w'];
			$where[] = " site_id = '$w' ";
		} else $w = false;

		if ( isset( $core->get['s'] ) && $s = $core->get['s'] ) {
			switch ( $s ) {				case 'w':	$where[] = " order_webstat < 5 "; break;
				case 'c':	$where[] = " order_webstat IN ( 5, 12 ) "; break;
				case 'a':	$where[] = " order_webstat BETWEEN 6 AND 11 "; break;
				default:	$s = false;
			}
		} else $s = false;

		$where = implode( ' AND ', $where );
		$sh = 30; $st = ( $page - 1 ) * $sh;
		$orders = $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE $where" );
		$order = $orders ? $core->db->data( "SELECT * FROM ".DB_ORDER." WHERE $where ORDER BY order_id DESC LIMIT $st, $sh" ) : array();

		$flow = $core->db->icol( "SELECT flow_id, flow_name FROM ".DB_FLOW." WHERE user_id = '".$core->user->id."' ".( $o ? " AND offer_id = '$o' " : '' )." ORDER BY flow_name ASC" );
		$offer = $core->wmsale->get( 'offers' );
		$sids = $core->db->col( "SELECT DISTINCT site_id FROM ".DB_ORDER." WHERE wm_id = '".$core->user->id."'" );
		$site = array(); foreach ( $sids as $ss ) $site[$ss] = $core->wmsale->get( 'site', $ss, 'site_url' );

		$core->mainline->add( $core->lang['stats_lead'] );
		$core->header();

		$core->tpl->load( 'body', 'lead' );
		if ( !defined( 'WORKFACE' )) $core->tpl->block( 'body', 'help' );

		$core->tpl->vars( 'body', array(
			'nostats'		=> $core->lang['nostats'],
			'date'			=> $core->lang['date'],
			'flow'			=> $core->lang['flow'],
			'offer'			=> $core->lang['offer'],
			'status'		=> $core->lang['status'],
			'show'			=> $core->lang['show'],
			'site'			=> $core->lang['site'],
			'space'			=> $core->lang['stat_spaces'],
			'calls'			=> $core->lang['order_calls_sh'],
			'reason'		=> $core->lang['comment'],
			'd'				=> $d,
			'u_stat'		=> $core->url( 'm', 'stats' ),
			'stat'			=> $core->lang['stats_date'],
			'pages'			=> pages ( $core->url( 'm', 'lead?' ) . ( $f ? 'f='.$f.'&' : '' ) . ( $d ? 'd='.$d.'&' : '' ) . ( $s ? 's='.$s.'&' : '' ) . ( $o ? 'o='.$o : '' ) . ( $w ? 'w='.$w : '' ), $orders, $sh, $page ),
			'shown'			=> sprintf( $core->lang['shown'], $st+1, min( $st+$sh, $orders ), $orders ),
		));

		foreach ( $offer as $of => $n ) {
			$core->tpl->block( 'body', 'offer', array( 'name' => $n, 'value' => $of, 'select' => ($of==$o) ? 'selected="selected"' : '' ) );
		}

		foreach ( $flow as $fl => $n ) {
			$core->tpl->block( 'body', 'flow', array( 'name' => $n, 'value' => $fl, 'select' => ($fl==$f) ? 'selected="selected"' : '' ) );
		}

		foreach ( $site as $sl => $n ) {
			$core->tpl->block( 'body', 'site', array( 'name' => $n, 'value' => $sl, 'select' => ($sl==$w) ? 'selected="selected"' : '' ) );
		}

		foreach ( $core->lang['stat_status'] as $st => $n ) {
			$core->tpl->block( 'body', 'status', array( 'name' => $n, 'value' => $st, 'select' => ($st==$s) ? 'selected="selected"' : '' ) );
		}

		if ( $orders ) foreach ( $order as $r ) {			$core->tpl->block( 'body', 'order', array(
				'offer'			=> $offer[$r['offer_id']],
				'site'			=> $core->wmsale->get( 'site', $r['site_id'], 'site_url' ),
				'space'			=> $core->wmsale->get( 'site', $r['space_id'], 'site_url' ),
				'flow'			=> $flow[$r['flow_id']],
				'ip'			=> int2ip( $r['order_ip'] ),
				'country'		=> $r['order_country'] ? $r['order_country'] : 'zz',
				'time'			=> smartdate( $r['order_time'] ),
				'stid'			=> ( $r['order_webstat'] < 6 || $r['order_webstat'] == 12 ) ? $r['order_webstat'] : 10,
				'status'		=> ( $r['order_webstat'] < 6 || $r['order_webstat'] == 12 ) ? $core->lang['statuso'][$r['order_webstat']] : $core->lang['statusok'],
				'calls'			=> $r['order_calls'],
				'reason'		=> $r['order_reason'] ? $core->lang['reasono'][$r['order_reason']] : ( ( $r['order_webstat'] == 5 || $r['order_webstat'] == 12 ) ? ( $r['order_comment'] ? sprintf( $core->lang['noreason_comment'], $r['order_comment'] ) :  $core->lang['noreason'] ) : ( $r['order_check'] ? $core->lang['stat_check'] : ( ( $r['order_webstat'] < 5 && $r['order_comment']) ? sprintf( $core->lang['noreason_comment'], $r['order_comment'] ) : '' ) )  ),
				'utm_id'		=> $core->lang['stat_srcsm'][$r['utm_id']],
				'utm_cn'		=> $r['utm_cn'],
				'utm_src'		=> $r['utm_src'],
			));
		} else $core->tpl->block( 'body', 'nostat' );

		$core->tpl->output( 'body' );

		$core->footer();
	  	$core->_die();

	  case 'stats':

        $today = date( 'Ymd' );
        $week1 = date( 'Ymd', strtotime( '-1 week' ) );

		extract(params( $core, array( 'to' => 'date', 'from' => 'date', 'o', 'f' ) ));
		if ( ! $to || $to > $today ) $to = $today;
        if ( $from > $to ) $from = $to;
        if ( ! $from ) $from = $week1;

		list( $offer, $flow, $stats ) = webmaster_clicks( $core, $core->user->id, $from, $to, $f, $o );
		$csv = ( $core->get['show'] == 'csv' ) ? 1 : 0;

		$core->mainline->add( $core->lang['stats_h'] );
		if ( $csv ) {
			header('Content-type: text/csv');
			header('Content-disposition: attachment;filename=stats.csv');
		} else $core->header();

		if ( $csv ) {
			$core->tpl->load( 'body', 'csv-stats' );
		} else $core->tpl->load( 'body', 'stats' );
		if ( !defined( 'WORKFACE' )) $core->tpl->block( 'body', 'help' );

		$core->tpl->vars( 'body', array(
			'nostats'		=> $core->lang['nostats'],
			'date'			=> $core->lang['date'],
			'wait'			=> $core->lang['stat_wait'],
			'accept'		=> $core->lang['stat_accept'],
			'cancel'		=> $core->lang['stat_cancel'],
			'spaces'		=> $core->lang['stat_spaces'],
			'clicks'		=> $core->lang['stat_clicks'],
			'unique'		=> $core->lang['stat_unique'],
			'flow'			=> $core->lang['flow'],
			'offer'			=> $core->lang['offer'],
			'show'			=> $core->lang['show'],
			'from'			=> date2form( $from ),
			'to'			=> date2form( $to ),
			'u_csv'			=> $core->url( 'm', 'stats?show=csv&from=' ) . date2form( $from ) . '&to=' . date2form( $to ) . ( $o ? '&o='.$o : '' ) . ( $f ? '&f='.$f : '' ),
		));

		foreach ( $offer as $of => $n ) {
			$core->tpl->block( 'body', 'offer', array( 'name' => $n, 'value' => $of, 'select' => ($of==$o) ? 'selected="selected"' : '' ) );
		}

		foreach ( $flow as $fl => $n ) {
			$core->tpl->block( 'body', 'flow', array( 'name' => $n, 'value' => $fl, 'select' => ($fl==$f) ? 'selected="selected"' : '' ) );
		}

		if ( $stats ) {
			foreach ( $stats as $d => &$s ) {
				$cl = max( $s['unique'], $s['suni'] );				$core->tpl->block( 'body', 'stat', array(
	            	'date'		=> date2form( $d ),
					'cr'		=> $cl ? sprintf( "%0.2f", ( $s['ca'] / $cl ) * 100 ) : 0,
					'epc'		=> $cl ? rur ( $s['sa'] / $cl ) : '-',
					'epcr'		=> $cl ? sprintf ( "%0.2f", $s['sa'] / $cl ) : '-',
	                'spaces'	=> (int) $s['spaces'],
	                'suni'		=> (int) $s['suni'],
	                'clicks'	=> (int) $s['clicks'],
	                'unique'	=> (int) $s['unique'],
	                'ca'		=> (int) $s['ca'],
	                'sa'		=> rur( $s['sa'] ),
	                'sar'		=> (int) $s['sa'],
					'ua'		=> $core->url( 'm', 'lead' ) . '?d=' . date2form( $d ) . '&s=a',
	                'cw'		=> (int) $s['cw'],
	                'sw'		=> rur( $s['sw'] ),
	                'swr'		=> (int) $s['sw'],
					'uw'		=> $core->url( 'm', 'lead' ) . '?d=' . date2form( $d ) . '&s=w',
	                'cc'		=> (int) $s['cc'],
	                'sc'		=> rur( $s['sc'] ),
	                'scr'		=> (int) $s['sc'],
					'uc'		=> $core->url( 'm', 'lead' ) . '?d=' . date2form( $d ) . '&s=c',
				));
			} unset ( $d, $s, $stats );
		} else $core->tpl->block( 'body', 'nostat' );

		$core->tpl->output( 'body', $csv ? 'windows-1251' : false  );

		if ( ! $csv ) $core->footer();
	  	$core->_die();

	  case 'sources':

        $today = date( 'Ymd' );
        $week1 = date( 'Ymd', strtotime( '-2 week' ) );

		extract(params( $core, array( 'to' => 'date', 'from' => 'date', 'o', 'f', 'c', 'q', 'g', 'a', 'fi'  ) ));
		if ( ! $to || $to > $today ) $to = $today;
        if ( $from > $to ) $from = $to;
        if ( ! $from ) $from = $week1;
        if ( ! $c ) $c = 10;

		if ( $core->user->level ) {
			$core->tpl->block( 'body', 'alls' );
			$all = $a ? true : false;
		} else $all = false;

		list ( $offer, $flow, $stats ) = webmaster_sources ( $core, $core->user->id, $from, $to, $o, $f, $g, $q, $c, $all, $fi );
		$gv = $g ? 'utm_src' : 'utm_cn';
		$og = $g ? 0 : 1;
		$csv = ( $core->get['show'] == 'csv' ) ? 1 : 0;

		$bls = $core->db->data( "SELECT * FROM ".DB_BL." WHERE user_id = '".$core->user->id."'" );
		$bl = array(); foreach ( $bls as $b ) $bl[$b['bl_type']][$b['bl_utm']][$b['bl_item']] = $b['bl_id'];

		$core->mainline->add( $core->lang['stats_src'] );
		if ( $csv ) {
			header('Content-type: text/csv');
			header('Content-disposition: attachment;filename=sources.csv');
		} else $core->header();

		if ( $csv ) {
			$core->tpl->load( 'body', 'csv-sources' );
		} else $core->tpl->load( 'body', 'sources' );
		if ( !defined( 'WORKFACE' )) $core->tpl->block( 'body', 'help' );

		$core->tpl->vars( 'body', array(
			'nostats'		=> $core->lang['nostats'],
			'type'			=> $core->lang['type'],
			'today'			=> $core->lang['today'],
			'source'		=> $core->lang['source'],
			'showall'		=> $core->lang['showall'],
			'all'			=> $all ? 'checked="checked"' : '',
			'wait'			=> $core->lang['stat_wait'],
			'accept'		=> $core->lang['stat_accept'],
			'cancel'		=> $core->lang['stat_cancel'],
			'spaces'		=> $core->lang['stat_spaces'],
			'clicks'		=> $core->lang['stat_clicks'],
			'unique'		=> $core->lang['stat_unique'],
			'total'			=> $core->lang['total'],
			'flow'			=> $core->lang['flow'],
			'offer'			=> $core->lang['offer'],
			'show'			=> $core->lang['show'],
			'help'			=> $core->lang['help'],
			'helptext'		=> $core->lang['stat_help'],
			'from'			=> date2form( $from ),
			'to'			=> date2form( $to ),
			'u_today'		=> $core->url( 'm', 'sources?from=' ) . date( 'Y-m-d' ) . '&to=' . date( 'Y-m-d' ) . ( $o ? '&o='.$o : '' ) . ( $f ? '&f='.$f : '' ) . ( $q ? '&q='.$q : '' ) . ( $g ? '&g='.$g : '' ) . ( $c ? '&c='.$c : '' ) . ( $all ? '&a='.$all : '' ),
			'u_csv'			=> $core->url( 'm', 'sources?show=csv&from=' ) . date2form( $from ) . '&to=' . date2form( $to ) . ( $o ? '&o='.$o : '' ) . ( $f ? '&f='.$f : '' ) . ( $q ? '&q='.$q : '' ) . ( $g ? '&g='.$g : '' ) . ( $c ? '&c='.$c : '' ) . ( $all ? '&a='.$all : '' )
		));

		foreach ( $offer as $of => $n ) {
			$core->tpl->block( 'body', 'offer', array( 'name' => $n, 'value' => $of, 'select' => ($of==$o) ? 'selected="selected"' : '' ) );
		}

		foreach ( $flow as $fl => $n ) {
			$core->tpl->block( 'body', 'flow', array( 'name' => $n, 'value' => $fl, 'select' => ($fl==$f) ? 'selected="selected"' : '' ) );
		}

		foreach ( $core->lang['stat_group'] as $gr => $n ) {
			$core->tpl->block( 'body', 'group', array( 'name' => $n, 'value' => $gr, 'select' => ($gr==$g) ? 'selected="selected"' : '' ) );
		}

		foreach ( $core->lang['stat_srcs'] as $sr => $n ) {
			if ( $sr ) $core->tpl->block( 'body', 'source', array( 'name' => $n, 'value' => $sr, 'select' => ($sr==$q) ? 'selected="selected"' : '' ) );
		}

		foreach ( $core->lang['stat_cutoff'] as $cc => $n ) {
			$core->tpl->block( 'body', 'cutoff', array( 'name' => $n, 'value' => $cc, 'select' => ($cc==$c) ? 'selected="selected"' : '' ) );
		}

		if ( $stats ) {

			if ( $g == 0 ) {
				$mids = array(); foreach ( $stats as &$s ) if ( $s['network'] == 1 ) $mids[] = $s['source'];
				$mban = $mids ? $core->db->icol( "SELECT img_mg, img_block FROM ".DB_IMAGE." WHERE img_mg IN ( ".implode( ',', $mids ) ." )" ) : array();
			} else $mban = array();

			foreach ( $stats as $d => &$s ) {

				list( $id, $src ) = explode( ':', $d );
				$inbl = $bl[$g][$id][$src];

				$tc = max( (int) $s['spaces'], (int) $s['clicks'], (int) $s['unique'] );
				$ts = $s['ct'] / $tc * 1000;
				$cls = $inbl ? 'grey' : ( ( $tc > 100 ) ? ( ( $ts < 1 ) ? 'red' : ( ( $ts < 10 ) ? 'yellow' : '' ) ) : '' );

				$core->tpl->block( 'body', 'stat', array(
					'u'			=> $core->url( 'm', 'sources?from=' ) . date2form( $from ) . '&to=' . date2form( $to ) . ( $o ? '&o='.$o : '' ) . ( $f ? '&f='.$f : '' ) . ( $og ? '&g='.$og : '' ) . '&q='. $id . '&c=1' . ( $all ? '&a='.$all : '' ) . '&fi=' . $src,
	            	'id'		=> $core->lang['stat_srcs'][$id],
	            	'src'		=> $src,
	            	'class'		=> $cls,
	            	'block'		=> isset( $mban[$src] ) ? ( $mban[$src] ? 'isbad' : 'isok' ) : '',
	                'spaces'	=> (int) $s['spaces'],
	                'suni'		=> (int) $s['suni'],
	                'clicks'	=> (int) $s['clicks'],
	                'unique'	=> (int) $s['unique'],
	                'ca'		=> (int) $s['ca'],
	                'cw'		=> (int) $s['cw'],
	                'cc'		=> (int) $s['cc'],
	                'ct'		=> (int) $s['ct'],
	                'bli'		=> $inbl ? $inbl : $g.'_'.$id.'_'.strtr( $src, '.', '_' ),
	                'blc'		=> $inbl ? 'decline red' : 'accept green',
	                'blu'		=> $inbl ? $core->url( 'a', 'bl-del', $inbl ).'?' : $core->url( 'a', 'bl-add', 0 ) . '?i='.$src . '&u='.$id . '&t='.$g,
	                'blt'		=> $inbl ? $core->lang['bl_del'] : $core->lang['bl_add'],
				));

			} unset ( $d, $s, $stats );
		} else $core->tpl->block( 'body', 'nostat' );

		$core->tpl->output( 'body', $csv ? 'windows-1251' : false );

		if ( ! $csv ) $core->footer();
	  	$core->_die();

	  case 'target':

		switch ( $message ) {
	    	case 'ok':		$core->info( 'info', 'done_add' );				break;
	    	case 'save':	$core->info( 'info', 'done_edit' );				break;
	    	case 'del':		$core->info( 'info', 'done_del' );				break;
    		case 'access':	$core->info( 'error', 'access_denied' );		break;
		}

		if ( $id ) {
			$tg = $core->db->row( "SELECT * FROM ".DB_TARGET." WHERE target_id = '$id' LIMIT 1" );
			if ( $tg['user_id'] != $core->user->id ) $core->go($core->url( 'mm', 'target', 'access' ));

			$types = array();
			foreach ( $core->lang['stat_tartype'] as $v => $n ) $types[] = array( 'name' => $n, 'value' => $v, 'select' => $v == $tg['target_type'] );

			$core->mainline->add( $core->lang['stats_target'], $core->url( 'm', 'target' ) );
			$core->mainline->add( $tg['target_name'] );
			$core->header();

		    $title	= $core->lang['target_edit_h'];
		    $action	= $core->url ( 'a', 'target-edit', $id );
		    $method	= 'post';
		    $field 	= array(
		    	array( 'type' => 'line', 'value' => $core->text->lines( $core->lang['target_edit_t'] ) ),
	            array( 'type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['name'],  'value' => $tg['target_name']),
	            array( 'type' => 'select', 'name' => 'type', 'head' => $core->lang['type'], 'value' => $types ),
		    );
		    $button = array(array('type' => 'submit', 'value' => $core->lang['save']));
		    $core->form ('targetedit', $action, $method, $title, $field, $button);

			$core->footer();
			$core->_die();

		}

        $today = date( 'Ymd' );
        $week1 = date( 'Ymd', strtotime( '-2 week' ) );

		extract(params( $core, array( 'to' => 'date', 'from' => 'date', 'o', 'f' ) ));
		if ( ! $to || $to > $today ) $to = $today;
        if ( $from > $to ) $from = $to;
        if ( ! $from ) $from = $week1;

		list ( $offer, $flow, $stats ) = webmaster_target ( $core, $core->user->id, $from, $to, $o, $f );
		$csv = ( $core->get['show'] == 'csv' ) ? 1 : 0;

		$core->mainline->add( $core->lang['stats_target'] );
		if ( $csv ) {
			header('Content-type: text/csv');
			header('Content-disposition: attachment;filename=target.csv');
		} else $core->header();

		if ( $csv ) {
			$core->tpl->load( 'body', 'csv-target' );
		} else $core->tpl->load( 'body', 'target' );

		$core->tpl->vars( 'body', array(
			'nostats'		=> $core->lang['nostats'],
			'type'			=> $core->lang['type'],
			'today'			=> $core->lang['today'],
			'target'		=> $core->lang['target'],
			'wait'			=> $core->lang['stat_wait'],
			'accept'		=> $core->lang['stat_accept'],
			'cancel'		=> $core->lang['stat_cancel'],
			'spaces'		=> $core->lang['stat_spaces'],
			'clicks'		=> $core->lang['stat_clicks'],
			'unique'		=> $core->lang['stat_unique'],
			'total'			=> $core->lang['total'],
			'flow'			=> $core->lang['flow'],
			'offer'			=> $core->lang['offer'],
			'show'			=> $core->lang['show'],
			'help'			=> $core->lang['help'],
			'helptext'		=> $core->lang['stat_help'],
			'confirm'		=> $core->lang['confirm'],
			'from'			=> date2form( $from ),
			'to'			=> date2form( $to ),
			'u_add'			=> $core->url( 'a', 'target-add', 0 ),
			'u_today'		=> $core->url( 'm', 'target?from=' ) . date( 'Y-m-d' ) . '&to=' . date( 'Y-m-d' ) . ( $o ? '&o='.$o : '' ) . ( $f ? '&f='.$f : '' ),
			'u_csv'			=> $core->url( 'm', 'target?show=csv&from=' ) . date2form( $from ) . '&to=' . date2form( $to ) . ( $o ? '&o='.$o : '' ) . ( $f ? '&f='.$f : '' )
		));

		foreach ( $offer as $of => $n ) $core->tpl->block( 'body', 'offer', array( 'name' => $n, 'value' => $of, 'select' => ($of==$o) ? 'selected="selected"' : '' ) );
		foreach ( $flow as $fl => $n ) $core->tpl->block( 'body', 'flow', array( 'name' => $n, 'value' => $fl, 'select' => ($fl==$f) ? 'selected="selected"' : '' ) );

		if ( $stats ) {
			foreach ( $stats as $d => &$s ) {

				$tc = max( (int) $s['spaces'], (int) $s['clicks'], (int) $s['unique'] );
				$ts = $tc ? $s['ct'] / $tc * 1000 : 0;
				$cls = $inbl ? 'grey' : ( ( $tc > 100 ) ? ( ( $ts < 1 ) ? 'red' : ( ( $ts < 10 ) ? 'yellow' : '' ) ) : '' );

				$core->tpl->block( 'body', 'stat', array(
					'id'		=> $d,
	            	'class'		=> $cls,
					'name'		=> $s['name'],
					'type'		=> (int) $s['type'],
	                'spaces'	=> (int) $s['space'],
	                'suni'		=> (int) $s['suni'],
	                'clicks'	=> (int) $s['clicks'],
	                'unique'	=> (int) $s['unique'],
	                'ca'		=> (int) $s['ca'],
	                'cw'		=> (int) $s['cw'],
	                'cc'		=> (int) $s['cc'],
	                'ct'		=> (int) $s['ct'],
					'edit'		=> $core->url( 'i', 'target', $d ),
					'del'		=> $core->url( 'a', 'target-del', $d ),
				));

			} unset ( $d, $s, $stats );
		} else $core->tpl->block( 'body', 'nostat' );

		$core->tpl->output( 'body', $csv ? 'windows-1251' : false );

		if ( ! $csv ) $core->footer();
	  	$core->_die();

	  case 'flowstat':

        $today = date( 'Ymd' );
        $week1 = date( 'Ymd', strtotime( '-2 week' ) );
        $yest = strtotime( '-1 day' );

		extract(params( $core, array( 'to' => 'date', 'from' => 'date' ) ));
		if ( ! $to || $to > $today ) $to = $today;
        if ( $from > $to ) $from = $to;
        if ( ! $from ) $from = $week1;

		$stats = webmaster_flowstat ( $core, $core->user->id, $from, $to );
		$csv = ( $core->get['show'] == 'csv' ) ? 1 : 0;

		$core->mainline->add( $core->lang['stats_flow'] );
		if ( $csv ) {
			header('Content-type: text/csv');
			header('Content-disposition: attachment;filename=flowstat.csv');
		} else $core->header();

		if ( $csv ) {
			$core->tpl->load( 'body', 'csv-flowstat' );
		} else $core->tpl->load( 'body', 'flowstat' );

		$core->tpl->vars( 'body', array(
			'nostats'		=> $core->lang['nostats'],
			'type'			=> $core->lang['type'],
			'today'			=> $core->lang['today'],
			'yesterday'		=> $core->lang['yesterday'],
			'target'		=> $core->lang['target'],
			'wait'			=> $core->lang['stat_wait'],
			'accept'		=> $core->lang['stat_accept'],
			'cancel'		=> $core->lang['stat_cancel'],
			'spaces'		=> $core->lang['stat_spaces'],
			'clicks'		=> $core->lang['stat_clicks'],
			'unique'		=> $core->lang['stat_unique'],
			'total'			=> $core->lang['total'],
			'flow'			=> $core->lang['flow'],
			'offer'			=> $core->lang['offer'],
			'show'			=> $core->lang['show'],
			'help'			=> $core->lang['help'],
			'helptext'		=> $core->lang['stat_help'],
			'confirm'		=> $core->lang['confirm'],
			'from'			=> date2form( $from ),
			'to'			=> date2form( $to ),
			'u_today'		=> $core->url( 'm', 'flowstat?from=' ) . date( 'Y-m-d' ) . '&to=' . date( 'Y-m-d' ) . ( $o ? '&o='.$o : '' ) . ( $f ? '&f='.$f : '' ),
			'u_yesterday'	=> $core->url( 'm', 'flowstat?from=' ) . date( 'Y-m-d', $yest ) . '&to=' . date( 'Y-m-d', $yest ) . ( $o ? '&o='.$o : '' ) . ( $f ? '&f='.$f : '' ),
			'u_csv'			=> $core->url( 'm', 'flowstat?show=csv&from=' ) . date2form( $from ) . '&to=' . date2form( $to ) . ( $o ? '&o='.$o : '' ) . ( $f ? '&f='.$f : '' )
		));

		if ( $stats ) {
			foreach ( $stats as $d => &$s ) {

				$tc = max( (int) $s['spaces'], (int) $s['clicks'], (int) $s['unique'] );
				$ts = $tc ? $s['ct'] / $tc * 1000 : 0;
				$cls = ( ( $tc > 100 ) ? ( ( $ts < 1 ) ? 'red' : ( ( $ts < 10 ) ? 'yellow' : '' ) ) : '' );

				$core->tpl->block( 'body', 'stat', array(
	            	'class'		=> $cls,
	            	'offer'		=> $s['offer'],
					'flow'		=> $s['flow'],
					'type'		=> (int) $s['type'],
	                'spaces'	=> (int) $s['space'],
	                'suni'		=> (int) $s['suni'],
	                'clicks'	=> (int) $s['clicks'],
	                'unique'	=> (int) $s['unique'],
	                'ca'		=> (int) $s['ca'],
	                'cw'		=> (int) $s['cw'],
	                'cc'		=> (int) $s['cc'],
	                'ct'		=> (int) $s['ct'],
				));

			} unset ( $d, $s, $stats );
		} else $core->tpl->block( 'body', 'nostat' );

		$core->tpl->output( 'body', $csv ? 'windows-1251' : false );

		if ( ! $csv ) $core->footer();
	  	$core->_die();

	  case 'bl':

		$bls = $core->db->data( "SELECT * FROM ".DB_BL." WHERE user_id = '".$core->user->id."' ORDER BY bl_type DESC, bl_utm ASC, bl_item ASC" );
		$bl = array( 1 => array(), 0 => array() ); foreach ( $bls as $b ) $bl[$b['bl_type']][$b['bl_utm']][$b['bl_item']] = $b['bl_id'];

		$core->mainline->add( $core->lang['black_list'] );
		$core->header();

		$core->tpl->load( 'body', 'bl' );

        foreach ( $bl as $blti => $blt ) {
        	$core->tpl->block( 'body', 'type', array(
        		'name'	=> $core->lang['bl_type'][$blti],
        		'url'	=> $core->url( 'a', 'bl-load', $blti ),
        	));

        	if ( $blt ) foreach ( $blt as $blui => $blu ) {
	        	$core->tpl->block( 'body', 'type.utm', array(
	        		'name' => $core->lang['stat_srcs'][$blui],
	        		'url'	=> $core->url( 'a', 'bl-load', $blti ) . '?u=' . $blui,
	        	));

				foreach ( $blu as $i => $v ) {                	$core->tpl->block( 'body', 'type.utm.item', array(
						'id'		=> $i,
		                'bli'		=> $v,
		                'blu'		=> $core->url( 'a', 'bl-del', $v ).'?',
		                'blt'		=> $core->lang['bl_del'],
                	));
				}

        	} else $core->tpl->block( 'body', 'type.no' );

        }

		$core->tpl->output( 'body' );

		$core->footer();
	  	$core->_die();

	  // Parked domains
	  case 'domain':

		switch ( $message ) {
	    	case 'ok':		$core->info( 'info', 'done_add' );				break;
	    	case 'del':		$core->info( 'info', 'done_del' );				break;
	    	case 'check':	$core->info( 'info', 'done_domain_check' );		break;
	    	case 'error':	$core->info( 'error', 'error_domain_check' );	break;
    		case 'access':	$core->info( 'error', 'access_denied' );		break;
		}

	    $core->mainline->add ( $core->lang['menu_domain'], $core->url( 'm', 'domain' ) );
	  	$core->header();

		$core->tpl->load( 'body', 'domain' );

		$core->tpl->vars( 'body', array(
			'text'		=> $core->text->lines( $core->lang['domain_t'] ),
			'u_add'		=> $core->url( 'a', 'dmn-add', 0 ),
			'url'		=> $core->lang['domain'],
			'status'	=> $core->lang['status'],
			'action'	=> $core->lang['action'],
			'check'		=> $core->lang['domain_check'],
			'del'		=> $core->lang['del'],
			'confirm'	=> $core->lang['confirm'],
			'nodomain'	=> $core->lang['nodomain'],
		));

		$domain = $core->db->data( "SELECT * FROM ".DB_DOMAIN." WHERE user_id = '".$core->user->id."' ORDER BY dom_status ASC, dom_url ASC" );
		if (count( $domain )) foreach ( $domain as $d ) {
			$core->tpl->block( 'body', 'domain', array(
				'url'		=> $d['dom_url'],
				'stclass'	=> $d['dom_status'] ? 'isok' : 'wait',
				'status'	=> $d['dom_status'] ? $core->lang['dom_ok'] : $core->lang['dom_wait'],
				'check'		=> $core->url( 'a', 'dmn-check', $d['dom_id'] ),
				'del'		=> $core->url( 'a', 'dmn-del', $d['dom_id'] ),
			));
		} else $core->tpl->block( 'body', 'nodoms' );


		$core->tpl->output( 'body' );

	  	$core->footer();
	  	$core->_die();

	  // WorkFlow
	  default:
	  case 'flow':

		switch ( $message ) {
	    	case 'ok':		$core->info( 'info', 'done_flow_ok' );		break;
	    	case 'save':	$core->info( 'info', 'done_flow_save' );	break;
	    	case 'del':		$core->info( 'info', 'done_flow_del' );		break;
	    	case 'error':	$core->info( 'error', 'error_flow' );		break;
    		case 'access':	$core->info( 'error', 'access_denied' );	break;
		}

		if ( $id ) {
			$flow = $core->db->row( "SELECT * FROM ".DB_FLOW." WHERE flow_id = '$id' LIMIT 1" );
			if ( $flow['user_id'] != $core->user->id ) $core->go($core->url( 'mm', '', 'access' ));

			$sitel = $core->wmsale->get( 'lands', $flow['offer_id'] );
			$sites = array();
			foreach ( $sitel as $k => $v ) $sites[] = array( 'name' => $v['site_url'], 'value' => $v['site_id'], 'select' => $v['site_id'] == $flow['flow_site'] );
			$spacl = $core->wmsale->get( 'space', $flow['offer_id'] );
			$space = array( array( 'name' => '&mdash;', 'value' => 0 ));
			foreach ( $spacl as $k => $v ) $space[] = array( 'name' => $v['site_url'], 'value' => $v['site_id'], 'select' => $v['site_id'] == $flow['flow_space'] );

		    $core->mainline->add ( $core->lang['menu_flow'], $core->url( 'm', 'flow' ) );
		    $core->mainline->add ( $flow['flow_name'] );
			$core->header();

		    $title	= $core->lang['flow_edit_h'];
		    $action	= $core->url ( 'a', 'flow-edit', $id );
		    $method	= 'post';
		    $field 	= array(
		    	array( 'type' => 'line', 'value' => $core->text->lines( $core->lang['flow_edit_t'] ) ),
	            array( 'type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['name'], 'descr' => $core->lang['flow_name_d'], 'value' => $flow['flow_name']),
	            array( 'type' => 'select', 'name' => 'site', 'head' => $core->lang['flow_land'], 'value' => $sites ),
	            array( 'type' => 'select', 'name' => 'space', 'head' => $core->lang['flow_space'], 'value' => $space ),
	            array( 'type' => 'checkbox', 'name' => 'cb', 'head' => $core->lang['flow_comeback'], 'checked' => $flow['flow_cb'] ),
	            array( 'type' => 'checkbox', 'name' => 'param', 'head' => $core->lang['flow_param'], 'checked' => $flow['flow_param'] ),
	            array( 'type' => 'text', 'length' => 200, 'name' => 'url', 'head' => $core->lang['flow_url'], 'descr' => $core->lang['flow_url_d'], 'value' => $flow['flow_url']),
	            array( 'type' => 'text', 'length' => 200, 'name' => 'pbu', 'head' => $core->lang['flow_pbu'], 'descr' => $core->lang['flow_pbu_d'], 'value' => $flow['flow_pbu']),
		    );
		    $button = array(array('type' => 'submit', 'value' => $core->lang['save']));
		    $core->form ('flowedit', $action, $method, $title, $field, $button);

			$core->footer();
			$core->_die();

		}

		$flows = $core->db->data( "SELECT * FROM ".DB_FLOW." WHERE user_id = '".$core->user->id."' ORDER BY flow_id DESC" );
		$flow = array(); foreach ( $flows as $f ) $flow[$f['offer_id']][] = $f;
		$redmn = $core->wmsale->get( 'domain', $core->user->id );

	    $core->mainline->add ( $core->lang['menu_flow'], $core->url( 'm', 'flow' ) );
	    $core->header ();

		$core->tpl->load( 'body', 'flows' );

		$core->tpl->vars( 'body', array(

			'text'		=> $core->text->lines( $core->lang['flows_text'] ),
			'flow_site'	=> $core->text->lines( $core->lang['flow_site'] ),
			'flow_cb'	=> $core->lang['flow_comeback'],
			'flow_sub'	=> $core->text->lines( $core->lang['flow_sub'] ),
			'flow_ajax'	=> $core->url( 'a', 'flow-ajax', 0 ),
			'flow_tgt'	=> $core->url( 'a', 'flow-target', 0 ),
			'flow_rd'	=> BASEURL,

			'u_stats'	=> $core->url( 'm', 'stats' ),
			'u_flowstat'=> $core->url( 'm', 'flowstat' ),
			'u_lead'	=> $core->url( 'm', 'lead' ),
			'u_sources'	=> $core->url( 'm', 'sources' ),
			'u_domain'	=> $core->url( 'm', 'domain' ),
			'u_target'	=> $core->url( 'm', 'target' ),

        	'name'		=> $core->lang['name'],
        	'action'	=> $core->lang['action'],
			'total'		=> $core->lang['total'],
			'offer'		=> $core->lang['offer'],
			'stats'		=> $core->lang['stats'],
			'url'		=> $core->lang['site'],
			'partner'	=> $core->lang['flow_partner_url'],
			'edit'		=> $core->lang['settings'],
			'del'		=> $core->lang['del'],
			'confirm'	=> $core->lang['flow_confirm'],

		));

		if ( $flow ) {
			foreach ( $flow as $o => $fl ) {
				$offer = $core->wmsale->get( 'offer', $o );
				$lands = $core->wmsale->get( 'lands', $o );
				$space = $core->wmsale->get( 'space', $o );

				$core->tpl->block( 'body', 'offer', array(
					'id'		=> $offer['offer_id'],
					'name'		=> $offer['offer_name'],
                	'url'		=> $core->url( 'i', 'offers', $o ),
                	'stats'		=> $core->url( 'm', 'stats' ) . '?o=' . $o,
                	'add'		=> $core->url( 'a', 'flow-add', $o ),
				));

				if ( $lands ) foreach ( $lands as &$ss ) {
                   	$core->tpl->block( 'body', 'offer.site', array(
						'id'	=> $ss['site_id'],
                       	'url'	=> $ss['site_url'],
						'epc'	=> sprintf( "%0.2f", $offer['offer_wm'] * $ss['site_convert'] ),
						'cr'	=> sprintf( "%0.2f", $ss['site_convert'] * 100 ),
					));
				} unset ( $ss );

				if ( $space ) {                   	$core->tpl->block( 'body', 'offer.subsite', array() );
					foreach ( $space as &$ss ) {
                    	$core->tpl->block( 'body', 'offer.subsite.s', array(
							'id'	=> $ss['site_id'],
	                       	'url'	=> $ss['site_url'],
							'epc'	=> sprintf( "%0.2f", $offer['offer_wm'] * $ss['site_convert'] ),
							'cr'	=> sprintf( "%0.2f", $ss['site_convert'] * 100 ),
						));
					} unset ( $ss );
				}

				if ( $redmn ) {                   	$core->tpl->block( 'body', 'offer.redmn', array() );
					foreach ( $redmn as &$redm ) {
                    	$core->tpl->block( 'body', 'offer.redmn.s', array( 'url' => $redm ));
					} unset ( $redm );
				}

				foreach ( $fl as $f ) {					$core->tpl->block( 'body', 'offer.flow', array(
						'id'		=> $f['flow_id'],
						'name'		=> $search ? $search->highlight( $f['flow_name'] ) : $f['flow_name'],
						'site'		=> $f['flow_site'],
						'space'		=> $f['flow_space'],
						'cb'		=> $f['flow_cb'],
						'param'		=> $f['flow_param'],
						'url'		=> $f['flow_url'],
						'pbu'		=> $f['flow_pbu'],
						'offer'		=> $offer[$f['offer_id']],
						'cr'		=> sprintf( "%0.2f", $f['flow_convert'] * 100 ),
						'epc'		=> rur( $f['flow_epc'] ),
						'total'		=> $f['flow_total'],
	                	'edit'		=> $core->url( 'i', 'flow', $f['flow_id'] ),
	                	'del'		=> $core->url( 'a', 'flow-del', $f['flow_id'] ),
	                	'stats'		=> $core->url( 'm', 'stats' ) . '?f=' . $f['flow_id'],
	                	'u_offer'	=> $core->url( 'm', 'stats' ) . '?o=' . $f['offer_id'],
					));
				}

			} unset ( $f, $flows );
		} else $core->tpl->block( 'body', 'noflow' );

		$core->tpl->output( 'body' );

		$core->footer ();
		$core->_die();

	}

	return false;

}