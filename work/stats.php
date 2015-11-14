<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			stats.php
 *  Description:	Statistics Cron Job
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
define ( 'ABSPATH', dirname(__FILE__).'/' );
define ( 'PATH', dirname(__FILE__).'/' );
require_once PATH . 'core/core.php';
require_once PATH_LIB. 'common.php';

// Stage 0. Prepare
$today = date( 'Ymd', strtotime( '-1 day' ) );
$week1 = date( 'Ymd', strtotime( '-8 days' ) );
$week2 = date( 'Ymd', strtotime( '-15 days' ) );
$week4 = date( 'Ymd', strtotime( '-30 days' ) );
$fw = strtotime( date2form( $week2 ) . ' 00:00:00' );
$ff = strtotime( date2form( $week1 ) . ' 00:00:00' );
$ft = strtotime( date2form( $week1 ) . ' 23:59:59' );
$tt = strtotime( date2form( $today ) . ' 23:59:59' );

// Stage 1. Counting clicks by day
$spaces = $core->db->icol( "SELECT flow_id, COUNT(*) FROM ".DB_CLICK." WHERE click_date = '$today' AND click_space = 1 GROUP BY flow_id" );
$suni	= $core->db->icol( "SELECT flow_id, COUNT(*) FROM ".DB_CLICK." WHERE click_date = '$today' AND click_space = 1 AND click_unique = 1 GROUP BY flow_id" );
$clicks = $core->db->icol( "SELECT flow_id, COUNT(*) FROM ".DB_CLICK." WHERE click_date = '$today' AND click_space = 0 GROUP BY flow_id" );
$unique = $core->db->icol( "SELECT flow_id, COUNT(*) FROM ".DB_CLICK." WHERE click_date = '$today' AND click_space = 0 AND click_unique = 1 GROUP BY flow_id" );
$fids = array_unique(array_merge( array_keys( $spaces ), array_keys( $clicks ) ));
$user = $fids ? $core->db->icol( "SELECT flow_id, user_id FROM ".DB_FLOW." WHERE flow_id IN ( ".implode( ',', array_keys( $fids ) )." )" ) : array();
foreach ( $fids as $f ) {	$core->db->add( DB_STATS, array( 'flow_id' => $f, 'user_id' => $user[$f], 'stat_date' => $today, 'stat_space' => $spaces[$f], 'stat_suni' => $suni[$f], 'stat_click' => $clicks[$f], 'stat_unique' => $unique[$f] ) );
} unset ( $clicks, $unique, $spaces, $suni, $users );

// Stage 2.1. Counting site convert
$sites = $core->db->icol( "SELECT site_id, COUNT(*) FROM ".DB_CLICK." WHERE click_space = 0 AND click_unique = 1 AND click_date BETWEEN '$week1' AND '$today' GROUP BY site_id" );
foreach ( $sites as $s => $c ) {
	$ords = $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE site_id = '$s' AND flow_id != 0 AND order_status > 5 AND order_status < 12 AND order_time BETWEEN '$ff' AND '$tt'" );
	$conv = $ords / $c;
	$core->db->edit( DB_SITE, array( 'site_convert' => $conv ), "site_id = '$s'" );
} unset ( $s, $sites );

// Stage 2.2. Counting spacing site convert
$sites = $core->db->icol( "SELECT site_id, COUNT(*) FROM ".DB_CLICK." WHERE click_space = 1 AND click_unique = 1 AND click_date BETWEEN '$week1' AND '$today' GROUP BY site_id" );
foreach ( $sites as $s => $c ) {
	$ords = $core->db->field( "SELECT COUNT(*) FROM ".DB_ORDER." WHERE space_id = '$s' AND flow_id != 0 AND order_status > 5 AND order_status < 12 AND order_time BETWEEN '$ff' AND '$tt'" );
	$conv = $ords / $c;
	$core->db->edit( DB_SITE, array( 'site_convert' => $conv ), "site_id = '$s'" );
} unset ( $s, $sites );

// Stage 3. Cleaning up the clicks and old orders
$core->db->del( DB_CLICK, "click_date < '$week4'" );
$orders = $core->db->col( "SELECT order_id FROM ".DB_ORDER." WHERE order_time < '$ff' AND order_status < 5" );
if ( $orders ) foreach ( $orders as $o ) order_edit( $core, $o, array( 'status' => 5, 'reason' => 6 ) );

// Stage 4. Order statistics from the 7th day
$orders = $core->db->data( "SELECT offer_id, flow_id, wm_id, order_status FROM ".DB_ORDER." WHERE flow_id != 0 AND order_time BETWEEN '$ff' AND '$ft'" );
$flowstat = array();
foreach ( $orders as &$o ) {	if ( ! $flowstat[$o['flow_id']] ) $flowstat[$o['flow_id']] = array( 'ca' => 0, 'sa' => 0, 'cc' => 0, 'sc' => 0 );
	$l = ( $o['order_status'] == 5 || $o['order_status'] > 10 ) ? 'c' : 'a';
	$flowstat[$o['flow_id']]['c'.$l] += 1;
	$flowstat[$o['flow_id']]['s'.$l] += $core->wmsale->price( $o['offer_id'], $o['wm_id'], 'wmp' );
} unset ( $o, $orders );
foreach ( $flowstat as $f => $s ) {	$core->db->edit( DB_STATS, array( 'count_accept' => $s['ca'], 'count_cancel' => $s['cc'], 'sum_accept' => $s['sa'], 'sum_cancel' => $s['sc'], ), "flow_id = $f AND stat_date = '$week1'" );
} unset ( $s, $f, $flowstat );

// Stage 5. Flow conversion counts
$fok = $core->db->icol( "SELECT flow_id, COUNT(*) FROM ".DB_ORDER." WHERE flow_id != 0 AND ( order_status BETWEEN 6 AND 10 ) AND ( order_time BETWEEN '$fw' AND '$tt' ) GROUP BY flow_id" );
$fcl = $core->db->data( "SELECT flow_id, user_id, SUM(stat_unique) AS `u`, SUM(stat_suni) AS `s` FROM ".DB_STATS." WHERE stat_date BETWEEN '$week2' AND '$today' GROUP BY flow_id" );
$flw = $fcl ? $core->db->icol( "SELECT flow_id, offer_id FROM ".DB_FLOW." WHERE flow_id IN ( ".implode( ',', array_keys( $fok ) )." )" ) : array();
$off = array();
foreach ( $fcl as $c ) {	$f = $c['flow_id'];	$cc = max( $c['s'], $c['u'] );
	$conv = $cc ? $fok[$f] / $cc : 0;
	$epc = $conv * $core->wmsale->price( $flw[$f], $c['user_id'], 'wmp' );
	$core->db->edit( DB_FLOW, array( 'flow_convert' => $conv, 'flow_epc' => $epc ), "flow_id = '$f'" );
	if ( !isset( $off[$flw[$f]] ) ) $off[$flw[$f]] = array( 'c' => 0, 'a' => 0 );
	$off[$flw[$f]]['c'] += $cc;
	$off[$flw[$f]]['a'] += $fok[$f];
} unset ( $c, $cc, $f, $fcl, $fcs, $fok );

// Stage 6. Offer conversion counts
foreach ( $off as $o => $d ) if ( $o ) {	$conv = $d['c'] ? $d['a'] / $d['c'] : 0;
	$core->db->edit( DB_OFFER, array( 'offer_convert' => $conv ), "offer_id = '$o'" );
}

// Stage 7. Offer gender counts
$ms = $core->db->icol( "SELECT offer_id, COUNT(*) FROM ".DB_ORDER." WHERE order_gender = 1 AND order_status BETWEEN 6 AND 11 GROUP BY offer_id" );
$fs = $core->db->icol( "SELECT offer_id, COUNT(*) FROM ".DB_ORDER." WHERE order_gender = 2 AND order_status BETWEEN 6 AND 11 GROUP BY offer_id" );
$offers = array_unique(array_merge( array_keys( $ms ), array_keys( $fs ) ));
foreach ( $offers as $o ) {	$total = $ms[$o] + $fs[$o];
	$mm = $ms[$o] / $total * 100;
	$ff = $fs[$o] / $total * 100;
	$core->db->edit( DB_OFFER, array( 'stat_m' => $mm, 'stat_f' => $ff ), "offer_id = '$o'" );
}

// Stage 8. Optimize used tables
$core->db->query( "OPTIMIZE TABLE ".DB_CLICK );
$core->db->query( "OPTIMIZE TABLE ".DB_STATS );
$core->db->query( "OPTIMIZE TABLE ".DB_ORDER );

// Stage 9. SMS statistics
curl( 'https://www.bytehand.com/login', 'remember=1&action=login&nick='.SMS_LOGIN.'&password='.SMS_PASS, array( 'cookie' => SMS_COOKIE ) );
$base = curl( 'https://www.bytehand.com/secure/outgoing?range=7_DAYS&action=export', false, array( 'cookie' => SMS_COOKIE ) );
$pbase = explode( "\n", $base ); unset ( $base );
$phone = array();
foreach ( $pbase as $i => $pl ) if ( $i ) {
	$phd = explode( ';', $pl );
	if ( $phd[4] == 'UNDELIVERABLE' ) $phone[] = $phd[1];
	if ( $phd[4] == 'DELETED' ) $phone[] = $phd[1];
	if ( $phd[4] == 'EXPIRED' ) $phone[] = $phd[1];
	if ( $phd[4] == 'REJECTED' ) $phone[] = $phd[1];
} unset ( $phd, $pl, $pbase );
$phone = array_unique( $phone );
$core->db->query( "UPDATE ".DB_ORDER." SET order_check = 1 WHERE order_phone IN ( '".implode("', '", $phone)."' ) AND order_status != 5 AND order_status < 10" );
require_once PATH . 'lib/ban.php';
$bans = check_phone_bans( $core, $phone );
foreach ( $phone as $p ) if ( ! $bans[$p] ) ban_phone( $core, $p );

// Stage 10. User flows statistics
$ufc = $core->db->icol( "SELECT user_id, COUNT(*) FROM ".DB_FLOW." GROUP BY user_id" );
$ftp = $core->db->data( "SELECT user_id, COUNT(*) AS `c`, SUM(flow_convert) AS `cr`, SUM(flow_epc) AS `epc` FROM ".DB_FLOW." WHERE flow_convert > 0 GROUP BY user_id" );
foreach ( $ftp as $f ) {
	$uid = $f['user_id'];
	$dte = array( 'user_flw' => $ufc[$uid], 'user_flwa' => $f['c'], 'user_cr' => $f['cr'] / $f['c'] * 100.0, 'user_epc' => $f['epc'] / $f['c'] );
	$core->db->edit( DB_USER, $dte, "user_id = '$uid'" );
}

// end. =)