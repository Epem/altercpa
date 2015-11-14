<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / offers.php
 *  Description:	Offers listing
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

function offers ( $core ) {

	$id = ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );
  	if ( $id && $core->wmsale->get( 'offer', $id, 'offer_active' ) ) {
  		$offer = $core->wmsale->get( 'offer', $id );
  		$sites = $core->wmsale->get( 'sites', $id );
  		if ( $core->user->work == 0 || $core->user->work == 2 ) {
			$flows = $core->db->data( "SELECT * FROM ".DB_FLOW." WHERE user_id = '".$core->user->id."' AND offer_id = '$id'" );
			$fc = count( $flows );
		} else $flows = $fc = false;

		$cntr = $offer['offer_country'] ? explode( ',', $offer['offer_country'] ) : array( 'ru' );
		$cntr = '<img src="/data/flag/' . implode( '.png" /> <img src="/data/flag/', $cntr ) . '.png" />';

		$core->mainline->add( $core->lang['offers_h'], $core->url( 'm', 'offers' ) );
		$core->mainline->add( $offer['offer_name'] );
		$core->header();

		$core->tpl->load( 'body', 'offer' );

		$price = $core->wmsale->price( $id, $core->user->id, 'wmp' );
		$ref = ( $core->user->vip && $offer['offer_ref_vip'] ) ? $offer['offer_ref_vip'] : $offer['offer_ref'];
		$prt = unserialize( $offer['offer_prt'] );
		if ( $prt[$core->user->id][2] ) $ref = $prt[$core->user->id][2];

		$core->tpl->vars( 'body', $offer );
		$core->tpl->vars( 'body', array(

        	'url'		=> $core->lang['site'],
        	'price'		=> $core->lang['price'],
        	'wm'		=> $core->lang['offer_towm'],
        	'ref'		=> $core->lang['offer_refs'],
        	'action'	=> $core->lang['action'],
        	'add'		=> $core->lang['offer_newflow'],
        	'confirm'	=> $core->lang['offer_confirm'],
			'logo'		=> sprintf( OFFER_LOGO, $offer['offer_id'] ),
			'u_add'		=> $core->url( 'a', 'flow-add', $offer['offer_id'] ),
			'text'		=> $offer['offer_text'] ? $core->text->out( $offer['offer_text'] ) : $core->lang['offer_notext'],

			'offer_wm'	=> $price,
			'offer_ref'	=> $ref ? rur( $ref ) : $core->lang['offer_noref'],
			'epc'		=> rur( $price * $offer['offer_convert'] ),
			'cr'		=> sprintf( "%0.2f", $offer['offer_convert'] * 100 ),
			'stat_m'	=> sprintf( "%0.1f", $offer['stat_m'] ),
			'stat_f'	=> sprintf( "%0.1f", $offer['stat_f'] ),
			'country'	=> $cntr,
			'status'	=> $fc ? sprintf( $core->lang['offer_flows'], $fc ) : $core->lang['offer_noflow'],

		));

		$counts = array( 'site' => 0, 'space' => 0 );
		foreach ( $sites as $s ) {
			$type = $s['site_type'] ? 'space' : 'site';
			$counts[$type] += 1;
			$core->tpl->block( 'body', $type, array(
				'u'		=> $s['site_url'],
				'epc'	=> rur( $price * $s['site_convert'] ),
				'cr'	=> sprintf( "%0.2f", $s['site_convert'] * 100 ),
			));
		} unset ( $s, $sites );
		foreach ( $counts as $c => $i ) if ( ! $i ) $core->tpl->block( 'body', 'no'.$c );

		if ( $core->user->work == 0 || $core->user->work == 2 ) {
            $core->tpl->block( 'body', 'wm' );
			if ( $fc ) {				foreach ( $flows as $f ) {
					$core->tpl->block( 'body', 'wm.flow', array(
						'stats'	=> $core->url( 'm', 'stats' ) . '?f=' . $f['flow_id'],
						'name'	=> $f['flow_name'],
						'epc'	=> rur( $f['flow_epc'] ),
						'cr'	=> sprintf( "%0.2f", $f['flow_convert'] * 100 ),
						'total'	=> rur( $f['flow_total'] ),
					));
				} unset ( $f, $flows );
			} else $core->tpl->block( 'body', 'wm.noflow' );
		}

		$name = 'stats' . $id; $tm = time(); $tv = $tm - 1800;
		$os = $core->cache->offer->{$name};
		if ( !$os || $os['valid'] < $tv ) {
			$os = array( 'valid' => $tm ); $tt = $tm - 2592000;
			$orders = $core->db->data( "SELECT order_time, order_webstat, order_reason FROM ".DB_ORDER." WHERE offer_id = '$id' AND order_time > '$tt'" );

			if ( count( $orders ) > 30 ) {

				$os['bt'] = array(); for ( $i = 0; $i < 24; $i++ ) $os['bt'][$i] = 0;
				$os['bd'] = array(); for ( $i = 0; $i < 7; $i++ ) $os['bd'][$i] = 0;
				$os['br'] = array(); foreach ( $core->lang['reasono'] as $i => $r ) $os['br'][$i] = 0;
				$os['bs'] = array( 'w' => 0, 'a' => 0, 't' => 0, 'c' => 0, 'cc' => 0, 'rc' => 0, 'na' => 0, 'nw' => 0, 'pr' => 0, 'to' => date( 'd.m.Y H:i:s'), 'from' => date( 'd.m.Y', $tt ) );

            	foreach ( $orders as $o ) {

            		if ( $o['order_webstat'] > 5 && $o['order_webstat'] < 12 ) {
	            		$os['bt'][ (int) date( 'H', $o['order_time'] ) ] += 1;
	            		$os['bd'][ (int) date( 'w', $o['order_time'] ) ] += 1;
	            		$os['bs']['t'] += 1; $os['bs']['a'] += 1;
            		} elseif ( $o['order_webstat'] == 5 ) {
						$os['br'][$o['order_reason']] += 1;
						$os['bs']['cc'] += 1;
                        if ( $o['order_reason'] < 6 ) {
							$os['bs']['c'] += 1;
							$os['bs']['t'] += 1;
						}
            		} else {
                     	switch ( $o['order_webstat'] ) {
                     		case 1:	$os['bs']['t'] += 1; $os['bs']['w'] += 1; $os['bs']['nw'] += 1; break;
                     		case 2:	$os['bs']['t'] += 1; $os['bs']['w'] += 1; $os['bs']['pr'] += 1; break;
                     		case 3:	$os['bs']['t'] += 1; $os['bs']['w'] += 1; $os['bs']['rc'] += 1; break;
                     		case 4:	$os['bs']['t'] += 1; $os['bs']['w'] += 1; $os['bs']['na'] += 1; break;
                     	}
            		}

            	}

            	$q = $os['bd'][0]; unset( $os['bd'][0] ); $os['bd'][0] = $q;
             	$bdmx = $btmx = 1;
				foreach ( $os['bd'] as $b ) $bdmx = max( $bdmx, $b );
				foreach ( $os['bt'] as $b ) $btmx = max( $btmx, $b );

				$os['bs']['w']	= sprintf( "%0.1f", $os['bs']['w'] / $os['bs']['t'] * 100 );
				$os['bs']['a']	= sprintf( "%0.1f", $os['bs']['a'] / $os['bs']['t'] * 100 );
				$os['bs']['c']	= sprintf( "%0.1f", $os['bs']['c'] / $os['bs']['t'] * 100 );
				$os['bs']['nw']	= sprintf( "%0.1f", $os['bs']['nw'] / $os['bs']['t'] * 100 );
				$os['bs']['na']	= sprintf( "%0.1f", $os['bs']['na'] / $os['bs']['t'] * 100 );
				$os['bs']['pr']	= sprintf( "%0.1f", $os['bs']['pr'] / $os['bs']['t'] * 100 );
				$os['bs']['rc']	= sprintf( "%0.1f", $os['bs']['rc'] / $os['bs']['t'] * 100 );

				foreach ( $os['bt'] as $t => $c ) $os['bt'][$t] = ceil ( $c / $btmx * 100 );
				foreach ( $os['bd'] as $d => $c ) $os['bd'][$d] = ceil ( $c / $bdmx * 100 );
				foreach ( $os['br'] as $r => $c ) $os['br'][$r] = sprintf ( "%0.1f", $c / $os['bs']['cc'] * 100 );

			} else $os['valid'] = false;

			$core->cache->offer->{$name} = $os;

		} $stats = $os['valid'] ? true : false;

		if ( $stats ) {			$core->tpl->block( 'body', 'bt', $os['bs'] );
			$core->tpl->block( 'body', 'bd', $os['bs'] );
			foreach ( $os['bt'] as $t => $c ) $core->tpl->block( 'body', 'bt.r', array( 't' => sprintf( "%02d", $t ), 'c' => $c ));
			foreach ( $os['bd'] as $d => $c ) $core->tpl->block( 'body', 'bd.r', array( 'd' => $core->lang['weekdays'][$d], 'c' => $c ));
			foreach ( $core->lang['reasono'] as $r => $t ) if ( $r > 0 && $r < 8 ) $core->tpl->block( 'body', 'bt.rs', array( 't' => $t, 'c' => $os['br'][$r] ));
		}

		$core->tpl->output( 'body' );

		$core->footer();

  	} else {

		$offers = $core->db->data( "SELECT * FROM ".DB_OFFER." WHERE offer_active = 1 ORDER BY offer_name ASC" );
  		if ( $core->user->work == 0 || $core->user->work == 2 ) {
			$flows = $core->db->icol( "SELECT offer_id, COUNT(*) FROM ".DB_FLOW." WHERE user_id = '".$core->user->id."' GROUP BY offer_id" );
		} else $flows = false;

		$core->mainline->add( $core->lang['offers_h'] );
		$core->header();

		$core->tpl->load( 'body', 'offers' );

		$core->tpl->vars( 'body', array(
			'text'		=> $core->text->lines( $core->lang['offers_text'] ),
        	'name'		=> $core->lang['name'],
        	'price'		=> $core->lang['price'],
        	'gender'	=> $core->lang['gender'],
        	'wm'		=> $core->lang['offer_towm'],
        	'ref'		=> $core->lang['offer_refs'],
        	'action'	=> $core->lang['action'],
        	'status'	=> $core->lang['status'],
        	'add'		=> $core->lang['offer_newflow'],
        	'confirm'	=> $core->lang['offer_confirm'],
		));

		foreach ( $offers as &$o ) {

			$cntr = $o['offer_country'] ? explode( ',', $o['offer_country'] ) : array( 'ru' );
			$cntr = '<img src="/data/flag/' . implode( '.png" /> <img src="/data/flag/', $cntr ) . '.png" />';

			$price = $core->wmsale->price( $o['offer_id'], $core->user->id, 'wmp' );
			$ref = ( $core->user->vip && $o['offer_ref_vip'] ) ? $o['offer_ref_vip'] : $o['offer_ref'];
			$prt = unserialize( $o['offer_prt'] );
			if ( $prt[$core->user->id][2] ) $ref = $prt[$core->user->id][2];

			$core->tpl->block( 'body', 'offer', array(
				'u'			=> $core->url( 'i', 'offers', $o['offer_id'] ),
				'id'		=> $o['offer_id'],
				'logo'		=> sprintf( OFFER_LOGO, $o['offer_id'] ),
				'add'		=> $core->url( 'a', 'flow-add', $o['offer_id'] ),
				'name'		=> $o['offer_name'],
				'text'		=> $o['offer_text'] ? $core->text->out( $o['offer_text'] ) : $core->lang['offer_notext'],
				'price'		=> $o['offer_price'],
				'country'	=> $cntr,
				'wm'		=> $price,
				'ref'		=> $ref ? rur( $ref ) : $core->lang['offer_noref'],
				'epc'		=> rur( $price * $o['offer_convert'] ),
				'cr'		=> sprintf( "%0.2f", $o['offer_convert'] * 100 ),
				'sclass'	=> $flows[$o['offer_id']] ? 'green' : 'grey',
				'status'	=> $flows[$o['offer_id']] ? sprintf( $core->lang['offer_flows'], $flows[$o['offer_id']] ) : $core->lang['offer_noflow'],
				'stat_m'	=> sprintf( "%0.1f", $o['stat_m'] ),
				'stat_f'	=> sprintf( "%0.1f", $o['stat_f'] ),
			));

			if ( $core->user->work == 0 || $core->user->work == 2 )  $core->tpl->block( 'body', 'offer.wm' );

		} unset ( $o, $offers );

		$core->tpl->output( 'body' );

		$core->footer();

	} $core->_die();

}

// lib-end. =)