<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / common.php
 *  Description:	Order common processing
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

// Get offer price
function offer_wmp ( $core, $offer, $user = false ) {	return $core->wmsale->price( $offer, $user, 'wmp' );
}

// Edit order info
function order_edit ( $core, $id, $info, $order = null ) {

	// Loading order info for processing of changes
	if ( ! $order ) $order = $core->db->row( "SELECT * FROM ".DB_ORDER." WHERE order_id = '$id' LIMIT 1" );
	$comp = $core->wmsale->get( 'comp', $order['comp_id'] );
	$changes = array();

	// Check order permissions
	if ( ! defined( 'INTHEWORK' ) ) {
		if (!( $core->user->level || $core->user->call )) if ( $order['comp_id'] != $core->user->comp ) return false;
	}

	// Basic info
	if ( $info['status']&& $info['status']!= $order['order_status'] )	$changes['order_status']= $info['status'];
	if ( $info['reason']&& $info['reason']!= $order['order_reason'] )	$changes['order_reason']= $info['reason'];
	if ( $info['user']	&& $info['user'] != $order['user_id'] )			$changes['user_id']		= $info['user'];
	if ( $info['comp']	&& $info['comp'] != $order['comp_id'] )			$changes['comp_id']		= $info['comp'];
	if ( $info['name']	&& $info['name'] != $order['order_name'] )		$changes['order_name']	= $info['name'];
	if ( $info['addr']	&& $info['addr'] != $order['order_addr'] )		$changes['order_addr'] 	= $info['addr'];
	if ( $info['area']	&& $info['area'] != $order['order_area'] )		$changes['order_area'] 	= $info['area'];
	if ( $info['city']	&& $info['city'] != $order['order_city'] )		$changes['order_city'] 	= $info['city'];
	if ( $info['street']&& $info['street'] != $order['order_street'] )	$changes['order_street']= $info['street'];
	if ( $info['index']	&& $info['index'] != $order['order_index'] )	$changes['order_index']	= $info['index'];
	if ( $info['phone']	&& $info['phone'] != $order['order_phone'] )	$changes['order_phone']	= $info['phone'];
	if ( $info['track']	&& $info['track'] != $order['track_code'] )		$changes['track_code']	= $info['track'];
	if ( $info['rec']	&& $info['rec'] != $order['order_recall'] )		$changes['order_recall']= $info['rec'];
	if ( $info['comment']&& $info['comment'] != $order['order_comment'] )$changes['order_comment']= $info['comment'];
	if ( $info['exto']	&& $info['exto'] != $order['ext_oid'] )			$changes['ext_oid'] 	= $info['exto'];
	if ( isset($info['check']) && $info['check'] != $order['order_check'] )	$changes['order_check']= $info['check'];

	// Order Metadata
	if (isset( $info['meta'] )) {
		ksort( $info['meta'] );
		$mm = serialize( $info['meta'] );
		if ( $mm != $order['order_meta'] ) $changes['order_meta'] = $mm;
	}

	// SPSR track info
	if (isset( $info['spsr'] )) {
		$spsr = $info['spsr'];
		$sd = serialize( $spsr );
		if ( $sd != $order['track_spsr'] ) $changes['track_spsr'] = $sd;
	} else $spsr = false;

	// Order Accepting
	if ( $info['accept'] ) {
     	$changes['order_status'] = $comp['autoaccept'] ? 10 : 6;
     	if ( ! $info['shave'] ) {
     		$sh = (int) $core->wmsale->get( 'ofp', $order['offer_id'], 'shave'.$order['comp_id'] );
     		if ( !$sh ) $sh = (int) $core->wmsale->get( 'ofp', $order['offer_id'], 'shave' );
     		if ( $sh ) $info['shave'] = ( rand( 0, 100 ) <= $sh ) ? 2 : 0;
     	}
	} elseif ( $order['order_status'] > 4 && $changes['order_status'] < 6 ) unset( $changes['order_status'] );

	// WebStatus and OrderStatus can be different
	if ( $changes['order_status'] ) {
		$shave = $info['shave'] ? (int) $info['shave'] : 0;
		if ( $changes['order_status'] > 4 ) {
			if ( $shave ) {
				$changes['order_webstat'] = 5;
				$changes['order_reason'] = rand( 0, 7 ) ? 3 : 2;
				$changes['order_shave'] = $shave;
			} elseif ( $order['order_status'] == $order['order_webstat'] ) $changes['order_webstat'] = $changes['order_status'];
		} else $changes['order_webstat'] = $changes['order_status'];
	}

	// Calls are incremental
	if ( $info['calls']	) $changes['order_calls'] = ( (int) $order['order_calls'] ) + $info['calls'];

	// Check the phone to be russian mobile +79etc
	if ( $changes['order_phone'] ) $changes['order_phone_ok'] = ( substr( $changes['order_phone'], 0, 2 ) == '79' ) ? 1 : 0;

	// Track code changes will null tracking status
	if ( $changes['track_code'] ) {
		$changes['track_on']		= $info['track_on'] ? $info['track_on'] : 0;
		$changes['track_check']		= $info['track_check'] ? $info['track_check'] : 0;
		$changes['track_date']		= '';
		$changes['track_status']	= '';
	}

	// Checking items and delivery
	if ( isset( $info['counts'] ) || isset( $info['delivery'] ) || isset( $info['discount'] ) /*|| isset( $info['present'] ) */|| isset( $info['more'] ) ) {

		// Load offer and it's variants info
		$offer = $core->wmsale->get( 'offer', $order['offer_id'] );
		$vars = ( $offer['offer_vars'] ) ? $core->wmsale->get( 'vars', $offer['offer_id'] ) : false;
		$order['items'] = $order['order_items'] ? unserialize( $order['order_items'] ) : array();

		// Process variants or a single offer
		if ( $vars ) {
			$items = isset( $info['counts'] ) ? $info['counts'] : $order['items'];
			$counts = $price = 0;
			foreach ( $vars as &$v ) if ( $items[$v['var_id']] ) {
				$counts += $items[$v['var_id']];
				$price += $items[$v['var_id']] * $v['var_price'];
			} unset ( $v, $vars );
			$changes['order_items'] = serialize( $items );
		} else {
			$counts	= isset( $info['counts'] ) ? $info['counts'][$offer['offer_id']] : $order['order_count'];
			$price	= $offer['offer_price'] * $counts;
		}

		// Process discounts and presents
     	$changes['order_discount'] = $discount = isset( $info['discount'] ) ? $info['discount'] : $order['order_discount'];
     	$changes['order_more'] = $more = isset( $info['more'] ) ? $info['more'] : $order['order_more'];
   		if ( $discount > 0 && $discount < 100 ) $price = ceil( $price * ( ( 100 - $discount ) / 100 ) );
   		if ( $more > 0 ) $price += $more;

		// Process delivery
		if ( $offer['offer_delivery'] ) {
       		$changes['order_delivery'] = $delivery = isset( $info['delivery'] ) ? $info['delivery'] : $order['order_delivery'];
       		$price += $core->lang['deliverp'][$delivery];
		}

		// Finally set order changes
		$changes['order_count'] = $counts;
		$changes['order_price'] = $price;

	}

	// UnReCall and UnTrack
	if ( ( $changes['order_status'] > 4 || $order['order_status'] > 4 ) && $order['order_recall'] ) $changes['order_recall'] = 0;
	if ( ( $changes['order_status'] > 9 || $order['order_status'] > 9 ) && $order['track_on'] ) {
		$changes['track_on'] = $changes['track_check'] = $changes['track_call'] = $changes['track_result'] = $changes['track_notify'] = 0;
		$changes['track_date'] = $changes['track_status'] = '';
	}

	// Order completion
	if ( $changes['order_status'] == 5 || $changes['order_status'] > 9 ) $changes['order_check'] = 0;
	if ( $order['order_check'] && $changes['order_status'] > 10 ) {
		if (!( $order['ext_id'] || $order['order_shave'] )) {
			require_once ( PATH_LIB . 'finance.php' );
			$f = new Finance( $core );
			$fins = $core->db->col( "SELECT cash_id FROM ".DB_CASH." WHERE order_id = '$id'" );
			foreach ( $fins as $fn ) $f->del( $fn );
			unset ( $f );
		}
		$changes['order_status'] = 12;
		if ( $order['order_status'] == $order['order_webstat'] ) $changes['order_webstat'] = 12;
	}

	// Update order in database
	if (count( $changes )) {
		$sqls = array();
		foreach ( $changes as $k => &$v ) $sqls[] = " $k = '$v' ";
		$sql = "UPDATE ".DB_ORDER." SET ".implode( ',', $sqls )." WHERE order_id = '$id' LIMIT 1";
		$result = $core->db->query( $sql );
		unset ( $sql, $k, $v );
	} else return false;

	// Post processing
	if ( $result ) {
		$st = $changes['order_status'];

		// Order confirmation
		if ( $info['accept'] ) {

			// Process payments
			if ( $shave != 1 ) {

				// Finance Operations
				if ( ! $offer ) $offer = $core->wmsale->get( 'offer', $order['offer_id'] );
				$comment = sprintf ( "%s - %s", $offer['offer_name'], $id );
				require_once ( PATH_LIB . 'finance.php' );
				$f = new Finance( $core );

				// Pricing
				$ut = $order['wm_id'];
				$uc = $comp['user_id'];
				if ( $ut && $uc ) {
					extract( $core->wmsale->price( $offer['offer_id'], array( $ut, $uc ) ) );
				} elseif ( $ut ) {
					extract( $core->wmsale->price( $offer['offer_id'], $ut ) );
				} elseif ( $uc ) {
					extract( $core->wmsale->price( $offer['offer_id'], $uc ) );
				} else $ext = $wmp = $wmu = $ref = $rep = $pay = $pyu = $sub = $sup = $ext = 0;

				// UpSale
				if ( $order['order_count'] > 1 ) {					if ( $wmu ) $wmp += $wmu * ( $order['order_count'] - 1 );
					if ( $pyu ) $pay += $pyu * ( $order['order_count'] - 1 );
				}

				// Process payments
				if ( $uc && $pay ) $f->add( $comp['user_id'], $id, -$pay, 2, $comment );
				if ( $shave < 1 && $ut && $wmp ) {
					$f->add( $ut, $id, $wmp, 3, $comment );
					$core->db->query( "UPDATE ".DB_FLOW." SET flow_total = flow_total + ".$wmp." WHERE flow_id = '".$order['flow_id']."' LIMIT 1" );
                   	if ( $ref && $rep ) {
                       	$f->add( $ref, $id, $rep, 7, $comment );
						$sup = ( $sup > $rep ) ? $sup - $rep : 0;
	                   	if ( $sub && $sup ) {	                       	$core->db->query( "UPDATE ".DB_USER." SET user_got = user_got + '$rep', user_sup = user_sup + '$sup' WHERE user_id = '$ut' LIMIT 1" );
	                       	$f->add( $sub, $id, $sup, 7, $comment );
	                   	} else $core->db->query( "UPDATE ".DB_USER." SET user_got = user_got + '$rep' WHERE user_id = '$ut' LIMIT 1" );
                   	}
				}

			}

			// Store processing
			$items = ( $changes['order_items'] ) ? unserialize( $changes['order_items'] ) : ( $order['order_items'] ? unserialize( $order['order_items'] ) : false );
			if ( ! $items ) {
				$counts = $changes['order_count'] ? $changes['order_count'] : $order['order_count'];
				$core->db->query( "UPDATE ".DB_STORE." SET store_count = store_count - $counts WHERE offer_id = '".$order['offer_id']."' AND comp_id = '".$order['comp_id']."' AND var_id = '0' LIMIT 1" );
               } else foreach ( $items as $i => $c ) $core->db->query( "UPDATE ".DB_STORE." SET store_count = store_count - $c WHERE offer_id = '".$order['offer_id']."' AND comp_id = '".$order['comp_id']."' AND var_id = '$i' LIMIT 1" );

		}

		// Sending SMS
		$pok = isset( $changes['order_phone_ok'] ) ? $changes['order_phone_ok'] : $order['order_phone_ok'];
		if ( $pok ) {
			$phone = isset( $changes['order_phone'] ) ? $changes['order_phone'] : $order['order_phone'];
			if ( $comp['sms_post'] && $changes['track_code'] ) sms( SMS_SIGN, $phone, sprintf( $core->lang['sms_send'], $id, $changes['track_code'] ) );
			if ( $st >= $order['order_status'] ) {
				if ( $comp['sms_accept'] && $st == 6 ) sms( SMS_SIGN, $phone, sprintf( $core->lang['sms_accept'], $id ) );
				if ( $comp['sms_spsr'] && $order['order_delivery'] == 2 && $st == 9 ) sms( SMS_SIGN, $phone, sprintf( $core->lang['sms_spsr'], $id, $order['track_code'] ) );
				if ( $comp['sms_rupo'] && $order['order_delivery'] == 1 && $st == 9 ) sms( SMS_SIGN, $phone, sprintf( $core->lang['sms_rupo'], $id, $order['track_code'] ) );
			}
		}

		// External processing
		if ( $order['ext_id'] && $changes['order_webstat'] && $order['order_webstat'] < 5 ) {

        	$ext = $core->wmsale->get( 'ext', $order['ext_id'] );
        	switch ( $changes['order_webstat'] ) {
        		case 3: $url = $ext['url_rc'] ? $ext['url_rc'] : false; break;
        		case 4: $url = $ext['url_nc'] ? $ext['url_nc'] : false; break;
        		case 5: $url = $ext['url_dec'] ? $ext['url_dec'] : false; break;
        		case 6: $url = $ext['url_acc'] ? $ext['url_acc'] : false; break;
        		case 10: $url = $ext['url_pay'] ? $ext['url_pay'] : false; break;
        		case 11: $url = $ext['url_ret'] ? $ext['url_ret'] : false; break;
        		case 12: $url = $ext['url_del'] ? $ext['url_del'] : false; break;
        		default: $url = false;
        	}

        	if ( $url ) {
				if ( ! $offer ) $offer = $core->wmsale->get( 'offer', $order['offer_id'] );
				$odata = $offer['offer_pars'] ? unserialize( $offer['offer_pars'] ) : false;
            	if ( preg_match_all( '#\{eval:\[(.*?)\]\}#si', $url, $ems ) ) foreach ( $ems[0] as $k => $v ) $url = str_replace( $v, eval( $ems[1][$k] ), $url );
            	$url = str_replace( '{id}', $order['order_id'], $url );
            	$url = str_replace( '{uid}', $order['ext_uid'], $url );
            	$url = str_replace( '{src}', $order['ext_src'], $url );
            	$url = str_replace( '{time}', time(), $url );
            	$url = str_replace( '{now}', date( 'd.m.Y H:i' ), $url );
            	$url = str_replace( '{reason}', rawurlencode( $core->lang['reasono'][ $changes['order_reason'] ? $changes['order_reason'] : $order['order_reason'] ] ), $url );
            	$url = str_replace( '{rcode}', $changes['order_reason'] ? $changes['order_reason'] : $order['order_reason'], $url );
            	$url = str_replace( '{price}', $changes['order_price'] ? $changes['order_price'] : $order['order_price'], $url );
            	$url = str_replace( '{count}', $changes['order_count'] ? $changes['order_count'] : $order['order_count'], $url );
            	foreach ( $offer as $k => $v ) $url = str_replace( "{offer:$k}", $v, $url );
            	if ( $odata ) foreach ( $odata as $k => $v ) $url = str_replace( "{data:$k}", $v, $url );
              	file_get_contents( $url );
        	}

		}

		// PostBack processing
		if ( $order['flow_id'] && $changes['order_webstat'] && $order['order_webstat'] < 5 ) {			if ( $pbu = $core->wmsale->get( 'flow', $order['flow_id'], 'flow_pbu' ) ) {

				$pbd = array(
					'id'		=> $order['order_id'],
					'offer'		=> $order['offer_id'],
					'flow'		=> $order['flow_id'],
					'target'	=> $order['target_id'],
					'site'		=> $order['site_id'],
					'space'		=> $order['space_id'],
					'count'		=> $changes['order_count'] ? $changes['order_count'] : $order['order_count'],
					'price'		=> $changes['order_price'] ? $changes['order_price'] : $order['order_price'],
					'status'	=> $changes['order_webstat'],
					'reason'	=> $changes['order_reason'] ? $changes['order_reason'] : $order['order_reason'],
					'utmi'		=> $order['utm_id'],
					'utms'		=> $order['utm_src'],
					'utmc'		=> $order['utm_cn'],
				);

				foreach ( $pbd as $pbk => $pbv ) $pbu = str_replace( '{'.$pbk.'}', $pbv, $pbu );
				curl( $pbu, $pbd );

			}
		}

		return true;

	} else return false;

}


// lib-end. =)