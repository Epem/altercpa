<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			cron.php
 *  Description:	Crontab Processing
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
error_reporting (0);
define ( 'IN_ALTERCMS_CORE_ONE', true );
define ( 'INTHEWORK', true );
define ( 'ABSPATH', dirname(__FILE__).'/' );
define ( 'PATH', dirname(__FILE__).'/' );
require_once PATH . 'core/core.php';
require_once PATH_LIB . 'common.php';
require_once PATH_LIB . 'track.php';
require_once PATH_LIB . 'spsr.php';

//
// Processing main cron cleanup routines
//

$core->cron->add ( 'control_cache', 	'cron_control_cache', 	10800 );
$core->cron->add ( 'control_session', 	'cron_control_session', 86400 );
$core->cron->add ( 'control_work', 		'cron_control_work', 	86400 );
$core->cron->add ( 'spsr_monitoring', 	'cron_spsr', 			3600 );

//
// Support Notifications
//

$tm5s = time() - 300;
$users = $core->db->icol( "SELECT user_id, user_mail FROM ".DB_USER." WHERE supp_new > 0 AND supp_notify = 0 AND supp_last < '$tm5s'" );
if ( $users ) {
	$core->db->query( "UPDATE ".DB_USER." SET supp_notify = 1 WHERE user_id IN ( ".implode( ', ', array_keys( $users ) )." )" );
	$core->email->send( $users, $core->lang['mail_support_h'], $core->lang['mail_support_t'] );
}

if ( defined( 'SUPPORT_NOTIFY' ) ) {
	$admins = $core->db->field( "SELECT COUNT(*) FROM ".DB_USER." WHERE supp_admin > 0 AND supp_notify = 0 AND supp_last < '$tm5s'" );
	if ( $admins ) {
		$core->db->query( "UPDATE ".DB_USER." SET supp_notify = 1 WHERE supp_admin > 0 AND supp_notify = 0 AND supp_last < '$tm5s'" );
		$core->email->send( SUPPORT_NOTIFY, $core->lang['mail_support_ah'], sprintf( $core->lang['mail_support_at'], $admins ) );
	}
}

//
// Security Control
//

// Suspicious users
$haveips = $badusers = $wb = array();
$ug = array_unique($core->db->col( "SELECT user_ip FROM ".DB_USER." WHERE user_work = 2" ));
$uq = $core->db->start( "SELECT user_id, user_ip, user_ban, user_warn FROM ".DB_USER." WHERE user_work = 0 AND user_ip > 0" );
foreach ( $uq as &$u ) $u = $u / 256;
while ( $uu = $core->db->one( $uq ) ) {
	$ugd = $uu['user_ip'] / 256;
	if (in_array( $uu['user_ip'], $ugd )) continue;
	if ( $haveips[$uu['user_ip']] ) {
		if (!( $uu['user_warn'] || $uu['user_ban'] )) $badusers[] = $uu['user_id'];
		if ( ! $wb[$haveips[$uu['user_ip']]] ) $badusers[] = $haveips[$uu['user_ip']];
	} else {
		$haveips[$uu['user_ip']] = $uu['user_id'];
		if ( $uu['user_warn'] || $uu['user_ban'] ) $wb[$uu['user_id']] = true;
	}
} $core->db->stop( $uq );
if ( $badusers ) {
	$badusers = array_unique( $badusers );
	$badusers = implode( ',', $badusers );
	$core->db->edit( DB_USER, array( 'user_warn' => 1 ), "user_id IN ( $badusers )" );
} unset( $haveips, $badusers );

//
// Integration
//

// Check existing orders
$comps = $core->db->data( "SELECT comp_id, int_chk_url, int_chk_field, int_chk_count, int_chk_format, int_chk_code, int_chk_pre FROM ".DB_COMP." WHERE int_chk = 1" );
foreach ( $comps as $c ) {

	$flds = unserialize( $c['int_chk_field'] );

	$offers = $core->db->col( "SELECT DISTINCT offer_id FROM ".DB_ORDER." WHERE comp_id = '".$c['comp_id']."' AND order_status BETWEEN 2 AND 4" );
	foreach ( $offers as $off ) {

		$ofps = $core->wmsale->get( 'ofp', $off );

		$idsw = $idew = $e2i = array();
		$q = $core->db->start( "SELECT order_id, ext_oid, order_status FROM ".DB_ORDER." WHERE offer_id = '$off' AND comp_id = '".$c['comp_id']."' AND order_status BETWEEN 2 AND 4" );
		while ( $r = $core->db->one( $q ) ) {
			$idsw[ $r['order_id'] ] = $r['order_status'];
			$idew[ $r['order_id'] ] = $r['ext_oid'];
			$e2i[ $r['ext_oid'] ] = $r['order_id'];
		} $core->db->stop( $q );

		$ideq = array_chunk( $idew, $c['int_chk_count'], true );
		$idsq = array_chunk( $idsw, $c['int_chk_count'], true );
		unset ( $idsw, $idew );

		foreach ( $idsq as $ii => $ids ) {

			$ide = $ideq[$ii];
			$idl = array_keys( $ids );
			$idsl = implode( ',', $idl );
			$idel = implode( ',', $ide );

			if ( $flds ) {
				$post = array();
				foreach ( $flds as $k => $v ) {
					if ( ! $k ) continue;
					$v = str_replace( '{idsl}', $idsl, $v );
					$v = str_replace( '{idel}', $idel, $v );
					if ( $v == '{ids}' ) $v = $idl;
					if ( $v == '{ide}' ) $v = $ide;
					$post[$k] = $v;
				}
			} else $post = false;

			$inid = $idl[0];
			$exid = $ide[$inid];

			$url = $c['int_chk_url'];
			if ( $c['int_chk_pre'] ) eval( $c['int_chk_pre'] );
			$url = str_replace( '{id}', $inid, $url );
			$url = str_replace( '{ext}', $exid, $url );
			$url = str_replace( '{idsl}', $idsl, $url );
			$url = str_replace( '{idel}', $idel, $url );
			foreach ( $ofps as $k => $v ) $url = str_replace( '{ofp:'.$k.'}', $v, $url );

			if ( !count( $post ) ) $post = false;
			if ( $data = curl( $url, $post ) ) {

				switch ( $c['int_chk_format'] ) {
	            	case 1: $data = json_decode( $data, true ); break;
	            	case 2: $data = unserialize( $data ); break;
	            	case 3: $data = simplexml_load_string( $data ); break;
				}

				eval( $c['int_chk_code'] );

			}

		}

	}

}

// Posting new orders
$comps = $core->db->data( "SELECT comp_id, int_add_url, int_add_pre, int_add_field, int_add_code FROM ".DB_COMP." WHERE int_add = 1" );
foreach ( $comps as $c ) {

	$flds = unserialize( $c['int_add_field'] );
	$ngo = $core->db->data( "SELECT * FROM ".DB_ORDER." WHERE comp_id = '".$c['comp_id']."' AND order_status = 1" );
	foreach ( $ngo as $o ) {

		$addr = $o['order_addr'];
		if ( $o['order_street'] ) $addr = $o['order_street'] . ', ' . $addr;
		if ( $o['order_city'] ) $addr = $o['order_city'] . ', ' . $addr;
		if ( $o['order_area'] ) $addr = $o['order_area'] . ', ' . $addr;
		if ( $o['order_index'] ) $addr = $o['order_index'] . ', ' . $addr;
		$addr = trim( $addr, ', ' );

		$ofps = $core->wmsale->get( 'ofp', $o['offer_id'] );
		$vars = $core->wmsale->get( 'vars', $o['offer_id'] );

		if ( $flds ) {
			$post = array();
			foreach ( $flds as $k => $v ) {
				if ( substr( $v, 0, 5 ) == '{ofp:' ) $v = $core->wmsale->get( 'ofp', $o['offer_id'], substr( $v, 5, -1 ) );
				if ( substr( $v, 0, 7 ) == '{offer:' ) $v = $core->wmsale->get( 'offer', $o['offer_id'], substr( $v, 7, -1 ) );
				if ( $v == '{id}' )		$v = $o['order_id'];
				if ( $v == '{ip}' )		$v = int2ip( $o['order_ip'] );
				if ( $v == '{wm}' )		$v = $o['wm_id'];
				if ( $v == '{name}' )	$v = $o['order_name'];
				if ( $v == '{phone}' )	$v = $o['order_phone'];
				if ( $v == '{addr}' )	$v = $addr;
				if ( $v == '{count}' )	$v = $o['order_count'];
				if ( $v == '{price}' )	$v = $o['order_price'];
				if ( $v == '{offer}' )	$v = $o['offer_id'];
				if ( $v == '{comment}' )$v = $o['order_comment'];
				if ( $v == '{country}' ) {
					$cntrs = $core->wmsale->get( 'offer', $o['offer_id'], 'offer_country' );
					$ofc = explode( ',', $cntrs );
					if (in_array( $o['order_country'], $ofc )) {
						$v = $o['order_country'];
					} else $v = 'ru';
				}
				$post[$k] = $v;
			}
		} else $post = false;

		$url = $c['int_add_url'];
		if ( $c['int_add_pre'] ) eval( $c['int_add_pre'] );
		$url = str_replace( '{id}', $o['order_id'], $url );
		$url = str_replace( '{ip}', int2ip( $o['order_ip'] ), $url );
		$url = str_replace( '{wm}', $o['wm'], $url );
		$url = str_replace( '{name}', strtr( $o['order_name'], ' ', '+' ), $url );
		$url = str_replace( '{phone}', $o['order_phone'], $url );
		$url = str_replace( '{addr}', strtr( $addr, ' ', '+' ), $url );
		$url = str_replace( '{count}', $o['order_count'], $url );
		foreach ( $ofps as $k => $v ) $url = str_replace( '{ofp:'.$k.'}', $v, $url );

		if ( $result = curl( $url, $post ) ) {
			$rid = (int) eval( $c['int_add_code'] );
			if ( $rid > 0 ) order_edit( $core, $o['order_id'], array( 'status' => 2, 'exto' => $rid ) );
			if ( $rid < 0 ) order_edit( $core, $o['order_id'], array( 'status' => 5, 'reason' => abs($rid) ) );
		}

	} unset ( $o, $ngo );

}

//
// Post Tracker and SPSR
//

// New track codes for Russian Post
$thetime = time();
$totrack = $core->db->data( "SELECT order_id, track_code FROM ".DB_ORDER." WHERE order_delivery = 1 AND track_on = 0 AND order_status = 8" );
if ( count( $totrack ) ) {	$ct = $thetime - 12800;
	foreach ( $totrack as &$t ) {
		PostTracker::check( $t['track_code'] );
		$core->db->query( "UPDATE ".DB_ORDER." SET track_on = 1, track_check = '$ct' WHERE order_id = '".$t['order_id']."' LIMIT 1" );
	} unset ( $t, $totrack, $pt );
}

// Processing track codes for now
$mt = $thetime - 14400;
$spsrs = array();
$sql = "SELECT order_id, comp_id, order_status, track_code, track_status, order_delivery FROM ".DB_ORDER." WHERE order_status IN ( 8, 9 ) AND track_on = 1 AND track_check < '$mt' LIMIT 1";
while ( $t = $core->db->row( $sql ) ) {
	// Different delivery types
	$status = $date = '';
	switch ( $t['order_delivery'] ) {
	  // Post Tracker for Russian Post
	  case 1:
	  	if ( $info = PostTracker::info( $t['track_code'] ) ) {

			$todo = true;
			foreach ( $info as $i ) {
            	if ( $i['pro'] == 'Возврат' ) {
            		order_edit( $core, $t['order_id'], array( 'status' => 11 ) );
            		$todo = false; break;
				}
			}

			$now = end( $info );
			$status = $status = sprintf( '%s - %s (%s, %s)', $now['pro'], $now['state'], $now['index'], $now['city'] );
			$date = $now['date'];

			if ( $todo && $status ) {
				if ( $now['pro'] == 'Вручение' ) {
                	order_edit( $core, $t['order_id'], array( 'status' => 10 ) );
				} elseif ( $t['order_status'] == 8 ) {
					$check = array( 'вручени', 'заберет отправление сам', 'временное отсутствие адресата' );
					foreach ( $check as $c ) if ( mb_stripos( $status, $c, 0, 'utf-8' ) !== false ) order_edit( $core, $t['order_id'], array( 'status' => 9 ) );
				}
			}

		} break;

	  // SPSR tracking
	  case 2:

		$info = SPSRtrack::info( $t['track_code'] );
		if ( $info ) {

			$now = end( $info );
			$status = $now['status'];
			$date = $now['date'];

			if ( $t['order_status'] == 8 ) {
				$codes = array( 10800, 10100, 11, 10400, 10600, 10201, 10301 );
            	$infos = array( 'CLCND', 'CLCTR', 'PMRDC', 'CLCDD', 'CLCCS', 'CLCCH', 'CLCLB' );
            	if ( in_array( $now['code'], $codes ) || in_array( $now['info'], $infos ) ) order_edit( $core, $t['order_id'], array( 'status' => 9 ) );
			}

			if ( $now['info'] == 'CLCDR' || $now['code'] == 11100 ) order_edit( $core, $t['order_id'], array( 'status' => 10 ) );
			if ( $now['info'] == 'CLCND' || $now['code'] == 10800 ) {
				if ( ! $spsrs[$t['comp_id']] ) {
					$cc = $core->wmsale->get( 'comp', $t['comp_id'] );
                	if ( $cc['comp_spsr'] && $cc['comp_spsr_login'] && $cc['comp_spsr_pass'] ) {
                		$spsrs[$t['comp_id']] = new SPSRtrack( $cc['comp_spsr_login'], $cc['comp_spsr_pass'], $cc['comp_spsr'], SPSR_COOKIE );
                	} else $spsrs[$t['comp_id']] = new SPSRtrack( SPSR_LOGIN, SPSR_PASS, SPSR_ID, SPSR_COOKIE );
				} $sp = $spsrs[$t['comp_id']];

				$parcel = $sp->parcel( $t['track_code'] );
				if ( $parcel['CurState'] ) $status = $parcel['CurState'];
            	$dd = strtotime( $now['date'] . ' ' . $now['time'] );
                if ( $dd && ( $thetime - $dd ) > 604800 ) order_edit( $core, $t['order_id'], array( 'status' => 11 ) );

			}

		} else $date = $status = false;
		break;

	  default: $date = $status = false;

	}

	// Update check info
	if ( $status ) {
		$core->db->query( "UPDATE ".DB_ORDER." SET track_check = '".time()."', track_date = '$date', track_status = '$status' WHERE order_id = '".$t['order_id']."' LIMIT 1" );
	} else $core->db->query( "UPDATE ".DB_ORDER." SET track_check = '".time()."' WHERE order_id = '".$t['order_id']."' LIMIT 1" );

}

// Processing SPSR show lists
function cron_spsr ( $core ) {

	$ct = time() - 14400;
	$accs = $core->db->data( "SELECT comp_spsr, comp_spsr_login, comp_spsr_pass FROM".DB_COMP." WHERE comp_spsr_login != ''" );
	foreach ( $accs as $a ) {

		$spsr = new SPSRtrack ( $a['comp_spsr_login'], $a['comp_spsr_pass'], $a['comp_spsr'], SPSR_COOKIE );
		$ids = $spsr->show ( date( 'Y-m-d' ), date( 'Y-m-d', strtotime( '-2 days' ) ) );
		unset ( $spsr );

		if ( count( $ids ) ) {
			$oids = implode( ',', array_keys( $ids ) );
			$orders = $core->db->icol( "SELECT order_id, order_status FROM ".DB_ORDER." WHERE track_on = 0 AND order_id IN ( $oids ) AND order_status BETWEEN 6 AND 9" );
			foreach ( $orders as $o => $s ) {
				$changes = array( 'track' => $ids[$o], 'track_on' => 1, 'track_check' => '$ct' );
				if ( $s < 9 ) $changes['status'] = 8;
				order_edit( $core, $o, $changes );
			} unset ( $ids, $oids, $o, $orders );
		}

	}

}

// Cron completed
$core->_die ();
// end. =)