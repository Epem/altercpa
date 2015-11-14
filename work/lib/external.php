<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / external.php
 *  Description:	External agancy statistics
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

function external_menu ( $core, $menu ) {

	array_push( $menu, 'stats', 'offers' );
	return $menu;

}

function external_module ( $core ) {
	$module	= ( $core->get['m'] ) ? $core->get['m'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );
	$page	= ( $core->get['page'] > 0 ) ? (int) $core->get['page'] : 1;
	$message = ( $core->get['message'] ) ? $core->get['message'] : null;

	if ( $module == 'offers' ) {		require_once PATH_LIB . 'offers.php';
		offers ( $core );
	}

  	$where = array( "ext_id = '".$core->user->ext."'" );

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

	if ( isset( $core->get['w'] ) && $core->get['w'] ) {
		$w = (int) $core->get['w'];
		$where[] = " site_id = '$w' ";
	} else $w = false;

	if ( isset( $core->get['s'] ) && $s = $core->get['s'] ) {
		switch ( $s ) {			case 'w':	$where[] = " order_webstat < 5 "; break;
			case 'c':	$where[] = " order_webstat IN ( 5, 12 ) "; break;
			case 'a':	$where[] = " order_webstat BETWEEN 6 AND 11 "; break;
			default:	$s = false;
		}
	} else $s = false;

	$where = implode( ' AND ', $where );
	$sh = 30; $st = ( $page - 1 ) * $sh;
	$orders = $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE $where " );
	$order = $orders ? $core->db->data( "SELECT * FROM ".DB_ORDER." WHERE $where ORDER BY order_id DESC LIMIT $st, $sh" ) : array();

	$offer = $core->wmsale->get( 'offers' );
	$site = $core->wmsale->get( 'lands' );

	$core->mainline->add( $core->lang['stats_lead'] );
	$core->header();

	$core->tpl->load( 'body', 'external' );

	$core->tpl->vars( 'body', array(
		'nostats'		=> $core->lang['nostats'],
		'date'			=> $core->lang['date'],
		'flow'			=> $core->lang['flow'],
		'offer'			=> $core->lang['offer'],
		'status'		=> $core->lang['status'],
		'show'			=> $core->lang['show'],
		'site'			=> $core->lang['site'],
		'calls'			=> $core->lang['order_calls_sh'],
		'reason'		=> $core->lang['order_reason'],
		'd'				=> $d,
		'u_stat'		=> $core->url( 'm', 'stats' ),
		'stat'			=> $core->lang['stats_date'],
		'pages'			=> pages ( $core->url( 'm', '?' ) . ( $f ? 'f='.$f.'&' : '' ) . ( $d ? 'd='.$d.'&' : '' ) . ( $s ? 's='.$s.'&' : '' ) . ( $o ? 'o='.$o : '' ) . ( $w ? 'w='.$w : '' ), $orders, $sh, $page ),
		'shown'			=> sprintf( $core->lang['shown'], $st+1, min( $st+$sh, $orders ), $orders ),
	));

	foreach ( $offer as $of => $n ) {
		$core->tpl->block( 'body', 'offer', array( 'name' => $n, 'value' => $of, 'select' => ($of==$o) ? 'selected="selected"' : '' ) );
	}

	foreach ( $site as $sl => $n ) {
		$core->tpl->block( 'body', 'site', array( 'name' => $n, 'value' => $sl, 'select' => ($sl==$w) ? 'selected="selected"' : '' ) );
	}

	foreach ( $core->lang['stat_status'] as $st => $n ) {
		$core->tpl->block( 'body', 'status', array( 'name' => $n, 'value' => $st, 'select' => ($st==$s) ? 'selected="selected"' : '' ) );
	}

	if ( $orders ) foreach ( $order as $r ) {		$core->tpl->block( 'body', 'order', array(
			'offer'			=> $offer[$r['offer_id']],
			'site'			=> $site[$r['site_id']],
			'uid'			=> (strlen($r['ext_uid'])>25) ? sprintf( '<input type="text" value="%s" class="intable-view" />', htmlspecialchars($r['ext_uid']) ) : $r['ext_uid'],
			'src'			=> $r['ext_src'],
			'ip'			=> int2ip( $r['order_ip'] ),
			'country'		=> $r['order_country'],
			'time'			=> smartdate( $r['order_time'] ),
			'stid'			=> ( $r['order_webstat'] < 6 || $r['order_webstat'] == 12 ) ? $r['order_webstat'] : 10,
			'status'		=> ( $r['order_webstat'] < 6 || $r['order_webstat'] == 12 ) ? $core->lang['statuso'][$r['order_webstat']] : $core->lang['statusok'],
			'edit'			=> $core->url ( 'i', 'order', $r['order_id'] ),
			'calls'			=> $r['order_calls'],
			'reason'		=> $r['order_reason'] ? $core->lang['reasono'][$r['order_reason']] : ( ( $r['order_webstat'] == 5 || $r['order_webstat'] == 12 ) ? $core->lang['noreason'] : ''  ),
		));
	} else $core->tpl->block( 'body', 'nostat' );

	$core->tpl->output( 'body' );

	$core->footer();
  	$core->_die();


}