<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			add.php
 *  Description:	Order adding processor
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
include PATH . 'core/core.php';
include PATH . 'lib/ncl.php';
header ( 'Content-Type: text/html; charset=utf-8' );

//
// New order
//

function neworder ( $core, $data, $file = false ) {

	$sid 	= (int) $data['site'];
	$spc 	= (int) $data['from'];
	$fid 	= (int) $data['flow'];
	$oid 	= (int) $data['offer'];
	$tgt 	= (int) $data['target'];
	$iptext	= $data['ip'];
	$ip		= ip2int( $iptext );
	$name	= $data['name'] ? $core->text->line( $data['name'] ) : 'Без Воображения';
	$ind	= (int) $data['index'];
	$area	= $core->text->line( $data['area'] );
	$city	= $core->text->line( $data['city'] );
	$street	= $core->text->line( $data['street'] );
	$addr	= $core->text->line( $data['addr'] );
	if ( $addr == 'Уточнить по телефону' ) $addr = '';
	if ( $addr == 'Адрес узнать по телефону' ) $addr = '';
	$comm	= $core->text->line( $data['comm'] );
	$phone 	= (string) trim(preg_replace( '#[^0-9]+#i', '', $data['phone'] ));
	$pres	= ( $data['present'] > 0 ) ? (int) $data['present'] : 0;
	$cnt	= ( $data['count'] > 0 ) ? (int) $data['count'] : 1;
	$more	= ( $data['more'] > 0 ) ? (int) $data['more'] : 0;
	$dsc	= ( $data['discount'] > 0 && $data['discount'] < 100 ) ? (int) $data['discount'] : 0;
	$cntr	= $data['country'] ? strtolower(substr( $core->text->link( $data['country'] ), 0, 2 )) : false;
	$dlvr	= ( $data['delivery'] > 0 ) ? (int) $data['delivery'] : 1;
	$exti	= (int) $data['exti'];
	$extu	= $exti ? preg_replace( '#[^0-9A-Za-z\_\-\.]+#i', '', $data['extu'] ) : 0;
	$exts	= $exti ? preg_replace( '#[^0-9A-Za-z\_\-\.]+#i', '', $data['exts'] ) : 0;
	$utmi	= (int) $data['utmi'];
	$utmc	= (int) $data['utmc'];
	$utms	= (int) $data['utms'];
	$items	= is_array( $data['items'] ) ? serialize( $data['items'] ) : '';
	$meta	= $data['meta'] ? addslashes(serialize(unserialize(stripslashes( $data['meta'] )))) : '';

	$addr1	= $core->text->line( $data['addr1'] );
	$addr2	= $core->text->line( $data['addr2'] );
	$addr3	= $core->text->line( $data['addr3'] );
	if ( $addr1 ) $addr .= ', ' . $addr1;
	if ( $addr2 ) $addr .= ', ' . $addr2;
	if ( $addr3 ) $addr .= ', ' . $addr3;

	if (!( $oid && $offer = $core->wmsale->get( 'offer', $oid ) )) return 'offer';
	$site = $sid ? $core->wmsale->get( 'site', $sid ) : false;
	$flow = $fid ? $core->wmsale->get( 'flow', $fid ) : false;
	$ext = $exti ? $core->wmsale->get( 'ext', $exti ) : false;

	$status = $data['status'] ? (int) $data['status'] : 1;
	if ( $status == 1 ) $status = ( $offer['offer_payment'] == 1 ) ? 0 : 1;

	$userid = $flow ? $flow['user_id'] : ( $ext ? $ext['user_id'] : false );
	if ( $userid && $core->user->get( $userid, 'user_ban' ) ) return 'security';

	if ( $phone ) {

		// Name and address
		$name = mb_ucwords( $name );
		if ( ! $ind ) {
			if ( preg_match ( '#^([0-9]+)#i', $addr, $ind ) ) {
				$ind = $ind[1];
				$ad = preg_split( '#[\s,\.]+#i', $addr, 2 );
				$addr = trim( $ad[1], ' ,' );
			} else $ind = '';
		}

		// Price, presents and discounts
		if ( $data['items'] ) {
			$price = $cnt = 0;
			$vars = $core->wmsale->get( 'vars', $offer['offer_id'] );
			foreach ( $vars as &$v ) if ( $data['items'][$v['var_id']] ) {
				$cnt += $data['items'][$v['var_id']];
				$price += $data['items'][$v['var_id']] * $v['var_price'];
			} unset ( $v, $vars );
		} else $price = $cnt * $offer['offer_price'];
		if ( $dsc ) $price = ceil( $price * ( ( 100 - $dsc ) / 100 ) );
		if ( $pres ) $price += $core->lang['presentp'][$pres];
		if ( $more ) $price += $more;
		if ( $offer['offer_delivery'] ) {
			$price += $core->lang['deliverp'][$dlvr];
		} else $dlvr = 0;

		// GeoIP data
		$geoipdata = geoip( $core, $iptext );
		if ( $geoipdata ) {
			$geoip = array(
				'geoip_country'		=> $geoipdata['country'],
				'geoip_city'		=> $geoipdata['city'],
				'geoip_region'		=> $geoipdata['region'],
				'geoip_district'	=> $geoipdata['district'],
				'geoip_lat'			=> $geoipdata['lat'],
				'geoip_lng'			=> $geoipdata['lng'],
			);
        	if ( !$cntr ) $cntr = $geoip['geoip_country'];
        	if ( !$addr && !$city ) $city = $geoip['geoip_city'];
        	if ( !$addr && !$area ) $area = $geoip['geoip_region'];
		} else $geoip = false;

		// Check IP and phone
		if ( $phone{0} == '9' && strlen($phone) == 10 ) $phone = '7' . $phone;
		if ( substr( $phone, 0, 2 ) == '89' ) $phone = '79' . substr( $phone, 2 );
		if ( substr( $phone, 0, 2 ) == '99' ) $phone = '79' . substr( $phone, 2 );
		$pok = ( substr( $phone, 0, 2 ) == '79' ) ? 1 : 0;

		// Check for bans
		$phs = $core->db->field( "SELECT `status` FROM ".DB_BAN_PH." WHERE `phone` = '$phone' LIMIT 1" );
		$ips = $core->db->field( "SELECT `status` FROM ".DB_BAN_IP." WHERE `ip` = '$ip' LIMIT 1" );
		if ( $phs || $ips ) return 'ban';

		// Guess gender automatically
		$nc = new NCLNameCaseRu();
		$gender = ( $nc->genderDetect( $name ) != NCL::$MAN ) ? 2 : 1;
		unset ( $nc );

		// Script based company guess
		$comp = 0;
		if ( $offer['offer_script'] ) {
			$scr = explode( "\n", $offer['offer_script'] );
			foreach ( $scr as $sc ) {

				// Prepare script line to process
				$sc = trim( $sc ); if ( ! $sc ) continue;

				// Get company for the script line
				if (preg_match( '/#([0-9]+)/si', $sc, $ms )) {
					$cms = $ms[1];
				} else continue;

				// Get type and ID to match
				if (preg_match( '#([a-z]+)\:([0-9]+)#si', $sc, $ms )) {
					$iid = $ms[2];
					$iit = $ms[1];
					if (!( $iid && $iit )) continue;
				} else continue;

				// Match if it matches
				switch ( $iit ) {
					case 'user':	if ( $flow['user_id'] == $iid ) $comp = $cms; break;
					case 'flow':	if ( $fid == $iid ) $comp = $cms; break;
					case 'site':	if ( $sid == $iid ) $comp = $cms; break;
					case 'space':	if ( $spc == $iid ) $comp = $cms; break;
					case 'ext':		if ( $exti == $iid ) $comp = $cms; break;
					case 'country':	if ( $cntr == $iid ) $comp = $cms; break;
				}

				if ( $comp ) break; // If script worked OK

			} unset ( $sc, $scr );
		}

		if ( ! $comp ) {
			if ( $offer['offer_mr'] && ! $site['site_comp'] ) {
				$ct = $core->db->field( "SELECT comp_id FROM ".DB_ORDER." WHERE order_time > '".( time() - 604800 )."' AND ( order_phone = '$phone' OR order_ip = '$ip' ) ORDER BY order_id DESC LIMIT 1" );
				$mrt = unserialize( $offer['offer_mrt'] );
				if (!( $ct && in_array( $ct, $mrt ) )) {
					if ( $mrt && $ct = wrand( $mrt ) ) {
		                $comp = $ct;
					} else $comp = $site['comp_id'];
				} else $comp = $ct;
			} else $comp = $site['comp_id'];
		}

		$data = array(
			'offer_id' 			=> $oid,
			'comp_id'			=> $comp,
			'wm_id'				=> $userid,
			'flow_id' 			=> $fid,
			'site_id' 			=> $sid,
			'space_id' 			=> $spc,
			'target_id' 		=> $tgt,
			'utm_id'			=> $utmi,
			'utm_src'			=> $utms,
			'utm_cn' 			=> $utmc,
			'ext_id'			=> $exti,
			'ext_uid' 			=> $extu,
			'ext_src' 			=> $exts,
			'order_time'		=> time(),
			'order_ip' 			=> $ip,
			'order_country'		=> $cntr,
			'order_name' 		=> $name,
			'order_gender'		=> $gender,
			'order_phone' 		=> $phone,
			'order_phone_ok'	=> $pok,
			'order_index' 		=> $ind,
			'order_area' 		=> $area,
			'order_city' 		=> $city,
			'order_street' 		=> $street,
			'order_addr' 		=> $addr,
			'order_items' 		=> $items,
			'order_meta' 		=> $meta,
			'order_count' 		=> $cnt,
			'order_present' 	=> $pres,
			'order_discount' 	=> $dsc,
			'order_delivery' 	=> $dlvr,
			'order_more' 		=> $more,
			'order_price' 		=> $price,
			'order_comment' 	=> $comm,
			'order_status' 		=> $status,
			'order_webstat' 	=> $status
		);
		if ( $geoip ) $data += $geoip;

		if ( $core->db->add( DB_ORDER, $data ) ) {

			$id = $core->db->lastid();

			if ( $file ) {
				if ( is_uploaded_file( $file['tmp_name'] ) ) {
					$dot = strrpos( $file['name'], '.' );
					$ext = strtolower(substr( $file['name'], $dot + 1 ));
					$name = $id .'-'. substr( $core->text->link( substr( $file['name'], 0, $dot ) ), 0, 90 ) . '.' . $ext;
					$goodext = array( 'jpg', 'jpeg', 'gif', 'png', 'zip', 'rar', 'rar5', '7z', 'cdr', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' );
					if ( in_array( $ext, $goodext ) ) {
						move_uploaded_file( $file['tmp_name'], sprintf( FILENAME, $name ) );
						$core->db->edit( DB_ORDER, array( 'order_file' => $name ), "order_id = '$id'" );
					}
				}
			}

			if ( $exti && $url = $core->wmsale->get( 'ext', $exti, 'url_new' ) ) {
            	if ( preg_match_all( '#\{eval:\[(.*?)\]\}#si', $url, $ems ) ) foreach ( $ems[0] as $k => $v ) $url = str_replace( $v, eval( $ems[1][$k] ), $url );
            	$url = str_replace( '{id}', $id, $url );
            	$url = str_replace( '{uid}', $extu, $url );
            	$url = str_replace( '{src}', $exts, $url );
            	$url = str_replace( '{time}', time(), $url );
            	$url = str_replace( '{price}', $price, $url );
            	$url = str_replace( '{count}', $cnt, $url );
				foreach ( $offer as $k => $v ) $url = str_replace( "{offer:$k}", $v, $url );
				$odata = $offer['offer_pars'] ? unserialize( $offer['offer_pars'] ) : false;
            	if ( $odata ) foreach ( $odata as $k => $v ) $url = str_replace( "{data:$k}", $v, $url );
				curl( $url );
			}

			// PostBack processing
			if ( $userid && ( $pbu = $core->wmsale->get( 'flow', $flw, 'flow_pbu' ) ) ) {

				$pbd = array(
					'id'		=> $id,
					'offer'		=> $oid,
					'flow'		=> $flw,
					'target'	=> $tgt,
					'site'		=> $sid,
					'space'		=> $spc,
					'count'		=> $cnt,
					'price'		=> $price,
					'status'	=> $$status,
				);

				foreach ( $pbd as $pbk => $pbv ) $pbu = str_replace( '{'.$pbk.'}', $pbv, $pbu );
				curl( $pbu, $pbd );

			}

			return (int) $id;

		} else return 'db';

	} else return 'data';

}

//
// Actions
//

$action = ( $core->get['a'] ) ? $core->get['a'] : null;
switch ( $action ) {

  case 'add':

	// Get site key and ID
	$sid = (int) $core->post['site'];
	$key = $core->text->link( $core->get['key'] );

	// Check the site
	if (!( $sid && $site = $core->wmsale->get( 'site', $sid ) )) {
		echo 'e:site';
		$core->_die();
	}

	// Check securitu
	if ( $site['site_key'] != $key && hash_hmac( 'sha1', http_build_query( $core->post ), $site['site_key'] ) != $key ) {
		echo 'e:key';
		$core->_die();
	}

	// Process order
	unset ( $core->post['status'], $core->post['items'] );
	$oid = neworder( $core, $core->post, $core->files['file'] ? $core->files['file'] : false );
	echo is_numeric( $oid ) ? 'ok:'.$oid : 'e:' . $oid;
	$core->_die();

  case 'ext':

	$postline = var_export( $core->post, true );
	$getline = $core->server['HTTP_HOST'] . $core->server['REQUEST_URI'];
	file_put_contents( PATH . 'data/logs/'.time().'-'.rand( 1, 10 ).'.txt', sprintf( "%s\r\n%s", $getline, str_replace( "\n", "\r\n", $postline ) ) );

	// Get request info
	$fields = array( 'offer', 'oid', 'site', 'space', 'key', 'exto', 'exti', 'extu', 'exts', 'country', 'name', 'phone', 'area', 'city', 'street', 'addr', 'addr1', 'addr2', 'addr3', 'ip', 'present', 'count', 'discount', 'status', 'comm', 'items', 'counts', 'more' );
	$data = array(); foreach ( $fields as $f ) if ( $core->post[$f] || $core->get[$f] ) $data[$f] = $core->post[$f] ? $core->post[$f] : $core->get[$f];

	// Get external ID
	$exti = (int) $data['exti'];
	$ext = $core->wmsale->get( 'ext', $exti );

	// Check security
	if ( $data['key'] != $ext['ext_key'] ) {
		echo json_encode(array( 'status' => 'error', 'error_text' => 'key' ));
		$core->_die();
	}

	// Count items
	if ( $data['items'] ) {
		$ii = explode( ',', $data['items'] );
		$cc = explode( ',', $data['counts'] );
		$data['items'] = array();
		foreach ( $ii as $q => $i ) if ( $i = (int) $i ) {
			if ( ! $data['items'][$i] ) {
				$data['items'][$i] = (int) $cc[$q];
			} else $data['items'][$i] += (int) $cc[$q];
		}
		$data['count'] = array_sum( $data['items'] );
		unset( $data['counts'] );
	} else unset( $data['items'], $data['counts'] );

	// Check offer ID
	if ( ! $data['offer'] ) {
		if ( ! $ext['code_offer'] ) {
			echo json_encode(array( 'status' => 'error', 'error_text' => 'no-offer-code' ));
			$core->_die();
    	} else {
			$data['offer'] = eval( $ext['code_offer'] );
	    	if ( ! $data['offer'] ) {
				echo json_encode(array( 'status' => 'error', 'error_text' => 'offer' ));
				$core->_die();
	    	}
	  	}
	}

	// Check for status
	if ( $ext['code_accept'] ) {
		$isok = eval( $ext['code_accept'] );
		if ( ! $isok ) {
			echo json_encode(array( 'status' => 'error', 'error_text' => 'status' ));
			$core->_die();
		} else $data['status'] = $isok;
	} else unset( $data['status'] );

	// Process order
	$oid = neworder( $core, $data, $core->files['file'] ? $core->files['file'] : false );
	if ( is_numeric( $oid ) ) {
		echo json_encode(array( 'status' => 'ok', 'id' => $oid ));
	} else echo json_encode(array( 'status' => 'error', 'error_text' => $oid ));
	$core->_die();

}

header( 'HTTP/1.0 404 Not Found' );
$core->_die();

// end. =)