<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / wmsale.php
 *  Description:	WebMaster and Sales API
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

// WebMaster and Sales API
class WMsale {
	// Inner variables
	private $core;
	private $cache;
    private $data;
    private $prd = array();

	// Prepare to work
	public function __construct ( $core ) {		$this->core = $core;
		$this->cache = $core->cache->sale;
	}
	public function __destruct () { }

	// Get cached data
	public function get( $type, $id = 0, $field = false ) {
		// Prepares
		$id = (int) $id;

		// Check the inside cache
		if ( isset( $this->data[$type][$id] ) ) {			return $field ? $this->data[$type][$id][$field] : $this->data[$type][$id];
		}

		// Check the CORE cache
		$cn = $type . $id;
		if ( $this->cache->{$cn} ) {			$this->data[$type][$id] = $this->cache->{$cn};
			return $field ? $this->data[$type][$id][$field] : $this->data[$type][$id];
		}

		// Check the CORE cache and load info
		switch ( $type ) {
		  case 'comp':
			$info = $this->core->db->row( "SELECT * FROM ".DB_COMP." WHERE comp_id = '$id' LIMIT 1" );
		  	break;

		  case 'comps':
			$info = $this->core->db->icol( "SELECT comp_id, comp_name FROM ".DB_COMP." ORDER BY comp_name ASC" );
		  	break;

		  case 'domain':
			$info = $this->core->db->icol( "SELECT dom_id, dom_url FROM ".DB_DOMAIN." WHERE user_id = '$id' ORDER BY dom_url ASC" );
		  	break;

		  case 'target':
			$info = $this->core->db->icol( "SELECT target_id, target_name FROM ".DB_TARGET." WHERE user_id = '$id' ORDER BY target_name ASC" );
		  	break;

		  case 'targets':
			$info = $this->core->db->data( "SELECT * FROM ".DB_TARGET." WHERE user_id = '$id' ORDER BY target_name ASC" );
		  	break;

		  case 'ext':
			$info = $this->core->db->row( "SELECT * FROM ".DB_EXT." WHERE ext_id = '$id' LIMIT 1" );
		  	break;

		  case 'exts':
			$info = $this->core->db->icol( "SELECT ext_id, ext_name FROM ".DB_EXT." ORDER BY ext_name ASC" );
		  	break;

		  case 'offer':
			$info = $this->core->db->row( "SELECT * FROM ".DB_OFFER." WHERE offer_id = '$id' LIMIT 1" );
		  	break;

		  case 'ofp':
			$info = $this->core->db->field( "SELECT offer_pars FROM ".DB_OFFER." WHERE offer_id = '$id' LIMIT 1" );
			$info = unserialize( $info );
		  	break;

		  case 'offers':
			$info = $this->core->db->icol( "SELECT offer_id, offer_name FROM ".DB_OFFER." ORDER BY offer_name ASC" );
		  	break;

		  case 'price':
			$info = $this->core->db->icol( "SELECT offer_id, offer_price FROM ".DB_OFFER );
		  	break;

		  case 'vars':
		  	$info = array();
			$ii = $this->core->db->data( "SELECT * FROM ".DB_VARS." WHERE offer_id = '$id' ORDER BY var_name ASC" );
			foreach ( $ii as $il ) $info[$il['var_id']] = $il;
			unset ( $il, $ii );
		  	break;

		  case 'flow':
			$info = $this->core->db->row( "SELECT * FROM ".DB_FLOW." WHERE flow_id = '$id' LIMIT 1" );
		  	break;

		  case 'flows':
			$info = $this->core->db->icol( "SELECT flow_id, flow_name FROM ".DB_FLOW." WHERE user_id = '$id'" );
		  	break;

		  case 'site':
			$info = $this->core->db->row( "SELECT * FROM ".DB_SITE." WHERE site_id = '$id' LIMIT 1" );
		  	break;

		  case 'sites':
			$info = $this->core->db->data( "SELECT * FROM ".DB_SITE." WHERE offer_id = '$id' ORDER BY site_url ASC" );
		  	break;

		  case 'lands':
			$info = $id ? $this->core->db->data( "SELECT * FROM ".DB_SITE." WHERE offer_id = '$id' AND site_type = 0 ORDER BY site_url ASC" ) :  $this->core->db->icol( "SELECT site_id, site_url FROM ".DB_SITE." WHERE site_type = 0 ORDER BY site_url ASC" );
		  	break;

		  case 'space':
			$info = $this->core->db->data( "SELECT * FROM ".DB_SITE." WHERE offer_id = '$id' AND site_type = 1 ORDER BY site_url ASC" );
		  	break;

		  case 'mans':
			$info = $this->core->db->icol( "SELECT user_id, user_name FROM ".DB_USER." WHERE user_comp = '$id' ORDER BY user_name ASC" );
		  	break;

		  case 'allman':
			$info = $this->core->db->icol( "SELECT user_id, user_name FROM ".DB_USER." WHERE user_work > 0 ORDER BY user_name ASC" );
		  	break;

		  default: return false;

		}

		$this->data[$type][$id] = $this->cache->{$cn} = $info;
		return $field ? $this->data[$type][$id][$field] : $this->data[$type][$id];

	}

	// Cleanup cached data
	public function clear ( $type, $id = 0 ) {		$id = (int) $id;		unset ( $this->data[$type][$id] );
		$this->cache->clear( $type . $id );
	}

	// Getting the price
	public function price ( $offer, $user = 0, $type = false ) {
		if ( ! $user ) $user = $this->core->user->id;
		if ( is_array( $user ) ) {			$comp = $user[1];
			$user = $user[0];
		} else $comp = false;

		$cachename = $comp ? "$user:$comp" : $user;

		if ( ! $this->prd[$offer][$cachename] ) {

			$o = $this->get( 'offer', $offer );
			$p = unserialize( $o['offer_prt'] );
			$u = $this->core->user->get( $user );
			$c = $this->core->user->get( $comp );

			$wmp = ( $u['user_vip'] && $o['offer_wm_vip'] ) ? $o['offer_wm_vip'] : $o['offer_wm'];
			$wmu = ( $u['user_vip'] && $o['offer_wmu_vip'] ) ? $o['offer_wmu_vip'] : $o['offer_wmu'];
			if ( $comp ) {				$pay = ( $c['user_vip'] && $o['offer_pay_vip'] ) ? $o['offer_pay_vip'] : $o['offer_pay'];
				$pyu = ( $c['user_vip'] && $o['offer_pyu_vip'] ) ? $o['offer_pyu_vip'] : $o['offer_pyu'];
			} else {
				$pay = ( $u['user_vip'] && $o['offer_pay_vip'] ) ? $o['offer_pay_vip'] : $o['offer_pay'];
				$pyu = ( $u['user_vip'] && $o['offer_pyu_vip'] ) ? $o['offer_pyu_vip'] : $o['offer_pyu'];
			}

			if ( $u['user_ext'] ) {				if ( $o['offer_wm_ext'] ) {
					$wmp = $o['offer_wm_ext'];
					$wmu = $o['offer_wmu_ext'];
				}
				if ( $o['offer_pay_ext'] ) {
					$pay = $o['offer_pay_ext'];
					$pyu = $o['offer_pyu_ext'];
				}
			}
			if ( $ref = $u['user_ref'] ) {
				$rv = $this->core->user->get( $ref, 'user_vip' );
				$rep = ( $rv && $o['offer_ref_vip'] ) ? $o['offer_ref_vip'] : $o['offer_ref'];
				if ( $sub = $u['user_sub'] ) {
					$sup = $p[$sub][2] ? $p[$sub][2] : 0;
				} else $sup = 0;
			} else $rep = $sup = $ref = $sub = 0;

			if (isset( $p[$user] )) {
				if ( $p[$user][0] ) { $wmp = $p[$user][0]; $wmu = $p[$user][3]; }
				if ( $p[$user][1] ) { $pay = $p[$user][1]; $pyu = $p[$user][4]; }
				if ( $p[$user][2] && $ref ) $rep = $p[$user][2];
			}

			if (isset( $p[$comp] )) {
				if ( $p[$comp][0] ) { $wmp = $p[$comp][0]; $wmu = $p[$comp][3]; }
				if ( $p[$comp][1] ) { $pay = $p[$comp][1]; $pyu = $p[$comp][4]; }
				if ( $p[$comp][2] && $ref ) $rep = $p[$comp][2];
			}

			if ( $ref && isset( $p[$ref] ) ) {
				if ( $p[$ref][0] ) { $wmp = $p[$ref][0]; $wmu = $p[$ref][3]; }
				if ( $p[$ref][1] ) { $pay = $p[$ref][1]; $pyu = $p[$ref][4]; }
				if ( $p[$ref][2] ) $rep = $p[$ref][2];
			}

			if ( $sub && isset( $p[$sub] ) ) {
				if ( $p[$sub][0] ) { $wmp = $p[$sub][0]; $wmu = $p[$sub][3]; }
				if ( $p[$sub][1] ) { $pay = $p[$sub][1]; $pyu = $p[$sub][4]; }
			}

			$this->prd[$offer][$cachename] = array( 'wmp' => $wmp, 'wmu' => $wmu, 'pay' => $pay, 'pyu' => $pyu, 'ref' => $ref, 'rep' => $rep, 'sub' => $sub, 'sup' => $sup, 'vip' => $u['user_vip'], 'ext' => $u['user_ext'] );

		}

		return $type ? $this->prd[$offer][$cachename][$type] : $this->prd[$offer][$cachename];

	}

}