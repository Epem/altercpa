<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / company.php
 *  Description:	Company control panel
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
// Statistic routines
//

// Collect stats
function company_stats ( $core, $renew = false ) {
	$name = 'comp' . $core->user->id;
	$stats = $core->cache->comp->{$name};

	if ( $stats['time'] < time() || $renew ) {
		$yt = time() - 86400;
		$today = mktime( 0, 0, 0, date('m'), date('d'), date('Y') );
		$yesterday = mktime( 0, 0, 0, date('m',$yt), date('d',$yt), date('Y',$yt) );
		$mans = implode( ',', array_keys( $core->wmsale->get( 'mans', $core->user->comp ) ) );

		$data = array(
			'today'		=> company_stats_parse ( $core->db, $core->user->comp, $mans, "order_status > 1 AND order_time > '$today'" ),
			'yest'		=> company_stats_parse ( $core->db, $core->user->comp, $mans, "order_status > 1 AND order_time > '$yesterday' AND order_time <= '$today'" ),
			'total'		=> company_stats_parse ( $core->db, $core->user->comp, $mans, "order_status > 1" ),
			'cancel'	=> company_stats_parse ( $core->db, $core->user->comp, $mans, "order_status = 5" ),
			'wait'		=> company_stats_parse ( $core->db, $core->user->comp, $mans, "order_status IN ( 2, 3, 4 )" ),
			'pack'		=> company_stats_parse ( $core->db, $core->user->comp, $mans, "order_status = 6" ),
			'send'		=> company_stats_parse ( $core->db, $core->user->comp, $mans, "order_status IN ( 7, 8, 9 )" ),
			'done'		=> company_stats_parse ( $core->db, $core->user->comp, $mans, "order_status = 10" ),
		);

		$stats = array( 'time' => time() + 900, 'data' => $data );
		$core->cache->comp->{$name} = $stats;

	}

	return $stats['data'];

}

// Load stats data from DB and return user-parsed array
function company_stats_parse ( $db, $comp, $mans, $where ) {
	return $db->icol( "SELECT user_id, COUNT(*) AS `cnt` FROM ".DB_ORDER." WHERE $where AND ( comp_id = '$comp' OR user_id IN ( $mans ) ) AND user_id > 0 GROUP BY user_id" );
}

//
// Module functions
//

function company_menu ( $core, $menu ) {
	array_push( $menu, 'comp' );
	return $menu;

}

function company_action ( $core ) {
	$action = ( $core->get['a'] ) ? $core->get['a'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );

	switch ( $action ) {

	  case 'comp-info':		// Company basic information

		$edit = array(
			'comp_name'		=> $core->text->line ( $core->post['name'] ),
			'comp_fio'		=> $core->text->line ( $core->post['fio'] ),
			'comp_phone'	=> $core->text->line ( $core->post['phone'] ),
			'comp_index'	=> preg_replace( '#([^0-9]+)#', '', $core->post['index'] ),
			'comp_addr'		=> $core->text->line ( $core->post['addr'] ),
			'comp_bank'		=> $core->text->line ( $core->post['bank'] ),
			'comp_acc'		=> preg_replace( '#([^0-9]+)#', '', $core->post['acc'] ),
			'comp_ks'		=> preg_replace( '#([^0-9]+)#', '', $core->post['ks'] ),
			'comp_bik'		=> preg_replace( '#([^0-9]+)#', '', $core->post['bik'] ),
			'comp_inn'		=> preg_replace( '#([^0-9]+)#', '', $core->post['inn'] ),
			'comp_spsr'		=> $core->text->line ( $core->post['spsr'] ),
			'comp_spsr_login'	=> $core->text->line ( $core->post['spsr_login'] ),
			'comp_spsr_pass'	=> $core->text->line ( $core->post['spsr_pass'] ),
			'comp_spsr_from'	=> $core->text->line ( $core->post['spsr_from'] ),
			'sms_accept'		=> $core->post['sms_accept'] ? 1 : 0,
			'sms_post'			=> $core->post['sms_post'] ? 1 : 0,
			'sms_spsr'			=> $core->post['sms_spsr'] ? 1 : 0,
			'sms_rupo'			=> $core->post['sms_rupo'] ? 1 : 0,
			'autoaccept'		=> $core->post['autoaccept'] ? 1 : 0,
			'callscheme'		=> $core->text->line ( $core->post['callscheme'] ),
		);

		if ( $core->db->edit( DB_COMP, $edit, "comp_id = '".$core->user->comp."'" ) ) {			$core->wmsale->clear( 'comp', $core->user->comp );
			$core->wmsale->clear( 'comps' );
			$core->go($core->url( 'mm', 'comp', 'save-ok' ));
	    } else $core->go($core->url( 'mm', 'comp', 'save-e' ));

	  case 'comp-income':	// Company store income

		foreach ( $core->post['store'] as $i => &$s ) {
			$i = (int) $i; if ( !$i ) continue;
			if ( is_array( $s ) ) {
				foreach ( $s as $j => &$v ) {
					$j = (int) $j; if ( !$j ) continue;
					$v = (int) $v; if ( !$v ) continue;
	             	if ( $sid = $core->db->field( "SELECT store_id FROM ".DB_STORE." WHERE comp_id = '".$core->user->comp."' AND offer_id = '$i' AND var_id = '$j' LIMIT 1" ) ) {
	                	$core->db->query( "UPDATE ".DB_STORE." SET store_count = store_count + '$v' WHERE store_id = '$sid' LIMIT 1" );
	             	} else $core->db->query( "INSERT INTO ".DB_STORE." ( comp_id, offer_id, var_id, store_count ) VALUES ( '".$core->user->comp."', '$i', '$j', '$v' )" );
				} unset ( $v );
			} else {
             	$s = (int) $s; if ( !$s ) continue;
             	if ( $sid = $core->db->field( "SELECT store_id FROM ".DB_STORE." WHERE comp_id = '".$core->user->comp."' AND offer_id = '$i' AND var_id = 0 LIMIT 1" ) ) {
                	$core->db->query( "UPDATE ".DB_STORE." SET store_count = store_count + '$s' WHERE store_id = '$sid' LIMIT 1" );
             	} else $core->db->query( "INSERT INTO ".DB_STORE." ( comp_id, offer_id, var_id, store_count ) VALUES ( '".$core->user->comp."', '$i', 0, '$s' )" );
			}
		} unset ( $s );

	  	$core->go($core->url( 'mm', 'comp', 'income' ));

	  case 'comp-store':	// Company store correction

		foreach ( $core->post['store'] as $i => &$s ) {			$i = (int) $i; if ( ! $i ) continue;
			if ( is_array( $s ) ) {				foreach ( $s as $j => &$v ) {					$j = (int) $j; if ( ! $j ) continue;
	             	$v = (int) $v;
	             	if ( $v ) {
	                	$core->db->query( "REPLACE INTO ".DB_STORE." ( comp_id, offer_id, var_id, store_count ) VALUES ( '".$core->user->comp."', '$i', '$j', '$v' )" );
	             	} else $core->db->query( "DELETE FROM ".DB_STORE." WHERE comp_id = '".$core->user->comp."' AND offer_id = '$i' AND var_id = '$j' LIMIT 1" );
				} unset ( $v );
			} else {             	$s = (int) $s;
             	if ( $s ) {                	$core->db->query( "REPLACE INTO ".DB_STORE." ( comp_id, offer_id, var_id, store_count ) VALUES ( '".$core->user->comp."', '$i', 0, '$s' )" );
             	} else $core->db->query( "DELETE FROM ".DB_STORE." WHERE comp_id = '".$core->user->comp."' AND offer_id = '$i' AND var_id = 0 LIMIT 1" );
			}
		} unset ( $s );

	  	$core->go($core->url( 'mm', 'comp', 'store' ));

	  case 'comp-add':		// Managers - add new

		$name 	= $core->text->line ( $core->post['name'] );
		$email	= $core->text->email ( $core->post['email'] );
		$pass	= $core->text->pass ( trim( $core->post['pass'] ) );

		$uid = $core->db->field( "SELECT user_id FROM ".DB_USER." WHERE user_mail = '$email' LIMIT 1" );
		if ( ! $uid ) {

		    $sql = "INSERT INTO ".DB_USER." SET user_name = '$name', user_mail = '$email', user_pass = '$pass', user_work = 1, user_comp = '".$core->user->comp."'";
		    if ( $name && $email && trim($core->post['pass']) && $core->db->query( $sql ) ) {				$core->wmsale->clear( 'mans', $core->user->comp );
				$core->wmsale->clear( 'allman' );
		        $core->go($core->url( 'mm', 'comp', 'add-ok' ));
		    } else $core->go($core->url( 'mm', 'comp', 'add-e' ));

		} else $core->go($core->url( 'mm', 'comp', 'exists' ));

	  case 'comp-edit':		// Managers - edit info

		$user = $core->db->row( "SELECT * FROM ".DB_USER." WHERE user_id = '$id' LIMIT 1" );
		if ( $user['user_comp'] && $user['user_comp'] == $core->user->comp && $core->user->copmad ) {

			$name 	= $core->text->line ( $core->post['name'] );
			$email	= $core->text->email ( $core->post['email'] );
			$pass	= $core->text->pass ( trim( $core->post['pass'] ) );
			$compad	= $core->post['compad'] ? 1 : 0;
			$tariff	= (int) $core->post['tariff'];

			if ( $email != $user['user_mail'] ) {				$uid = $core->db->field( "SELECT user_id FROM ".DB_USER." WHERE user_mail = '$email' LIMIT 1" );
				if ( $uid ) $core->go($core->url( 'mm', 'comp', 'exists' ));
			}

			$pass_sql = trim( $core->post['pass'] ) ? ", user_pass = '$pass'" : '';

		    $sql = "UPDATE ".DB_USER." SET user_name = '$name', user_compad = '$compad', user_mail = '$email', user_tariff = '$tariff' $pass_sql WHERE user_id = '$id' LIMIT 1";
		    if ( $core->db->query ($sql) ) {
				$core->wmsale->clear( 'mans', $core->user->comp );
				$core->wmsale->clear( 'allman' );
				$core->cache->user->clear( 'user' . $id );
				$core->go($core->url( 'mm', 'comp', 'edit-ok' ));
		    } else $core->go($core->url( 'mm', 'comp', 'edit-e' ));

		} else $core->go($core->url( 'mm', 'comp', 'access' ));

	  case 'comp-del':		// Managers - delete

		$user = $core->db->row( "SELECT * FROM ".DB_USER." WHERE user_id = '$id' LIMIT 1" );
		if ( $id != 1 && $user['user_comp'] && $user['user_comp'] == $core->user->comp && $core->user->copmad ) {
			$core->db->query( "DELETE FROM ".DB_CASH." WHERE user_id = '$id'" );
			if ( $core->db->query ( "DELETE FROM ".DB_USER." WHERE user_id = '$id'" ) ) {
				$core->wmsale->clear( 'mans', $core->user->comp );
				$core->wmsale->clear( 'allman' );
				$core->go($core->url( 'mm', 'comp', 'del-ok' ));
			} else $core->go($core->url( 'mm', 'comp', 'del-e' ));
		} else $core->go($core->url( 'mm', 'comp', 'access' ));

	}

	return false;

}

function company_module ( $core ) {
	$module	= ( $core->get['m'] ) ? $core->get['m'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );
	$page	= ( $core->get['page'] > 0 ) ? (int) $core->get['page'] : 1;
	$message = ( $core->get['message'] ) ? $core->get['message'] : null;

	$comp 	= $core->wmsale->get( 'comp', $core->user->comp );
	switch ( $module ) {

	  case 'comp-info':

		$core->mainline->add ( $core->lang['company'], $core->url( 'm', 'comp' ) );
		$core->mainline->add ( $core->lang['comp_info_h'] );
		$core->header ();

		$title	= $core->lang['comp_info_h'];
		$action	= $core->url ( 'a', 'comp-info', '' );
		$method	= 'post';
		$field 	= array(
			array( 'type' => 'line', 'value' => $core->text->lines( $core->lang['comp_info_t'] ) ),
			array( 'type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['name'], 'value' => $comp['comp_name']),
			array( 'type' => 'text', 'length' => 100, 'name' => 'fio', 'head' => $core->lang['comp_name'], 'descr' => $core->lang['comp_name_d'], 'value' => $comp['comp_fio']),
			array( 'type' => 'text', 'length' => 100, 'name' => 'phone', 'head' => $core->lang['phone'], 'value' => $comp['comp_phone']),
			array( 'type' => 'text', 'length' => 100, 'name' => 'addr', 'head' => $core->lang['address'], 'descr' => $core->lang['comp_addr_d'], 'value' => $comp['comp_addr']),
			array( 'type' => 'text', 'length' => 8, 'name' => 'index', 'head' => $core->lang['index'], 'descr' => $core->lang['comp_index_d'], 'value' => $comp['comp_index']),
			array( 'type' => 'head', 'value' => $core->lang['comp_banking'] ),
			array( 'type' => 'text', 'length' => 100, 'name' => 'bank', 'head' => $core->lang['comp_bank'], 'descr' => $core->lang['comp_bank_d'], 'value' => $comp['comp_bank']),
			array( 'type' => 'text', 'length' => 15, 'name' => 'bik', 'head' => $core->lang['comp_bik'], 'value' => $comp['comp_bik']),
			array( 'type' => 'text', 'length' => 30, 'name' => 'acc', 'head' => $core->lang['comp_acc'], 'value' => $comp['comp_acc']),
			array( 'type' => 'text', 'length' => 30, 'name' => 'ks', 'head' => $core->lang['comp_ks'], 'value' => $comp['comp_ks']),
			array( 'type' => 'text', 'length' => 15, 'name' => 'inn', 'head' => $core->lang['comp_inn'], 'descr' => $core->lang['comp_inn_d'], 'value' => $comp['comp_inn']),
			array( 'type' => 'head', 'value' => $core->lang['comp_delivery'] ),
			array( 'type' => 'text', 'length' => 30, 'name' => 'spsr', 'head' => $core->lang['comp_spsr'], 'descr' => $core->lang['comp_spsr_d'], 'value' => $comp['comp_spsr']),
			array( 'type' => 'text', 'length' => 50, 'name' => 'spsr_login', 'head' => $core->lang['login'], 'value' => $comp['comp_spsr_login']),
			array( 'type' => 'text', 'length' => 50, 'name' => 'spsr_pass', 'head' => $core->lang['pass'], 'value' => $comp['comp_spsr_pass']),
			array( 'type' => 'text', 'length' => 50, 'name' => 'spsr_from', 'head' => $core->lang['city'], 'value' => $comp['comp_spsr_from']),
			array( 'type' => 'head', 'value' => $core->lang['comp_sms'] ),
            array( 'type' => 'checkbox', 'name' => 'sms_accept', 'head' => $core->lang['comp_sms_accept'], 'descr' => $core->lang['comp_sms_accept_d'], 'checked' => $comp['sms_accept'] ),
            array( 'type' => 'checkbox', 'name' => 'sms_post', 'head' => $core->lang['comp_sms_post'], 'descr' => $core->lang['comp_sms_post_d'], 'checked' => $comp['sms_post'] ),
            array( 'type' => 'checkbox', 'name' => 'sms_spsr', 'head' => $core->lang['comp_sms_spsr'], 'descr' => $core->lang['comp_sms_spsr_d'], 'checked' => $comp['sms_spsr'] ),
            array( 'type' => 'checkbox', 'name' => 'sms_rupo', 'head' => $core->lang['comp_sms_rupo'], 'descr' => $core->lang['comp_sms_rupo_d'], 'checked' => $comp['sms_rupo'] ),
            array( 'type' => 'checkbox', 'name' => 'autoaccept', 'head' => $core->lang['comp_autoaccept'], 'descr' => $core->lang['comp_autoaccept_d'], 'checked' => $comp['autoaccept'] ),
            array( 'type' => 'text', 'name' => 'callscheme', 'head' => $core->lang['comp_callscheme'], 'descr' => $core->lang['comp_callscheme_d'], 'value' => $comp['callscheme'] ),
		);
		$button = array(array('type' => 'submit', 'value' => $core->lang['save']));
		$core->form ('comp', $action, $method, $title, $field, $button);

		$core->footer ();
		$core->_die();

	  case 'comp-store': $isstore = true;
	  case 'comp-income':

		$oids = $core->db->col( "SELECT offer_id FROM ".DB_SITE." WHERE comp_id = '".$core->user->comp."'" );
		$stores = $core->db->data( "SELECT * FROM ".DB_STORE." WHERE comp_id = '".$core->user->comp."'" );
		$store = array();
		foreach ( $stores as &$s ) {
			if ( ! isset( $store[$s['offer_id']] ) ) $store[$s['offer_id']] = array( 'count' => 0, 'vars' => array() );
			if ( $s['var_id'] ) {
				$store[$s['offer_id']]['vars'][$s['var_id']] = array( 'count' => $s['store_count'] );
			} else $store[$s['offer_id']]['count'] = $s['store_count'];
			$oids[] = $s['offer_id'];
		} unset ( $s, $stores );
		$oids = implode( ', ', array_unique( $oids ) );

		// Storage offers
		$offers = $oids ? $core->db->data( "SELECT offer_id, offer_name FROM ".DB_OFFER." WHERE offer_id IN ( $oids )" ) : array();
		foreach ( $offers as &$o ) {
			$store[$o['offer_id']]['name'] = $o['offer_name'];
		} unset ( $o, $offers );

		// Storage offer variants
		$vars = $oids ? $core->db->data( "SELECT offer_id, var_id, var_name, var_price FROM ".DB_VARS." WHERE offer_id IN ( $oids )" ) : array();
		foreach ( $vars as &$o ) {
			$store[$o['offer_id']]['vars'][$o['var_id']]['name'] = $o['var_name'];
		} unset ( $o, $vars );

		$core->mainline->add ( $core->lang['company'], $core->url('m', 'comp') );
		$core->mainline->add ( $isstore ? $core->lang['comp_store_h'] : $core->lang['comp_income_h'] );
		$core->header ();

		$title	= $isstore ? $core->lang['comp_store_h'] : $core->lang['comp_income_h'];
		$action	= $core->url ( 'a', $isstore ? 'comp-store' : 'comp-income', '' );
		$method	= 'post';
		$field 	= array(array( 'type' => 'line', 'value' => $core->text->lines( $isstore ? $core->lang['comp_store_t'] : $core->lang['comp_income_t'] ) ));

		foreach ( $store as $i => &$s ) {			$field[] = array( 'type' => 'head', 'value' => $s['name'] );
			if ( $s['vars'] ) {            	foreach ( $s['vars'] as $j => &$v ) {					$field[] = array( 'type' => 'text', 'name' => "store[$i][$j]", 'head' => $v['name'], 'value' => $isstore ? (int) $v['count'] : '' );
            	} unset ( $j, $v );
			} else $field[] = array( 'type' => 'text', 'name' => "store[$i]", 'head' => $s['name'], 'value' => $isstore ? (int) $s['count'] : '' );
		} unset ( $s, $store );

		$button = array(array('type' => 'submit', 'value' => $isstore ? $core->lang['save'] : $core->lang['comp_income_do'] ));
		$core->form ('store', $action, $method, $title, $field, $button);

		$core->footer ();
		$core->_die();

	  case 'comp':

		switch ( $message ) {
	    	case 'save-ok':		$core->info( 'info', 'done_comp_save' ); break;
	    	case 'add-ok':		$core->info( 'info', 'done_user_add' ); break;
	    	case 'edit-ok':		$core->info( 'info', 'done_user_edit' ); break;
	    	case 'del-ok':		$core->info( 'info', 'done_user_del' ); break;
	    	case 'income':		$core->info( 'info', 'done_comp_income' ); break;
	    	case 'store':		$core->info( 'info', 'done_comp_store' ); break;
	    	case 'save-e':		$core->info( 'error', 'error_comp_save' ); break;
	    	case 'add-e':		$core->info( 'error', 'error_user_add' ); break;
	    	case 'edit-e':		$core->info( 'error', 'error_user_edit' ); break;
	    	case 'del-e':		$core->info( 'error', 'error_user_del' ); break;
	    	case 'exists':		$core->info( 'error', 'error_user_exist' ); break;
	    	case 'access':		$core->info( 'error', 'access_denied' ); break;
		}

		if ( $id ) {
			$user = $core->db->row ( "SELECT * FROM ".DB_USER." WHERE user_id = '$id' LIMIT 1" );
			if ( $user['user_comp'] != $core->user->comp ) $core->go($core->url( 'mm', 'comp', 'access' ));

			$core->mainline->add ( $core->lang['company'], $core->url('m', 'comp') );
			$core->mainline->add ( $user['user_name'] );
			$core->header ();

			$title	= $core->lang['user_edit'];
			$action	= $core->url ( 'a', 'comp-edit', $id );
			$method	= 'post';
			$field 	= array(
				array( 'type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['user_name'], 'descr' => $core->lang['user_name_d'], 'value' => $user['user_name']),
				array( 'type' => 'text', 'length' => 100, 'name' => 'email', 'head' => $core->lang['user_email'], 'descr' => $core->lang['user_email_d'], 'value' => $user['user_mail']),
				array( 'type' => 'pass', 'length' => 32, 'name' => 'pass', 'head' => $core->lang['user_pass'], 'descr' => $core->lang['user_pass_d'] ),
				array( 'type' => 'checkbox', 'name' => 'compad', 'head' => $core->lang['user_compad'], 'descr' => $core->lang['user_compad_d'], 'checked' => $user['user_compad'] ),
	            array( 'type' => 'text', 'length' => 5, 'name' => 'tariff', 'head' => $core->lang['tariff'],  'value' => $user['user_tariff']),
			);
			if ( $core->user->work < $user['user_work'] ) $field[] = array( 'type' => 'line', 'value' => $core->text->lines( $core->lang['comp_user_work'] ) );
			$button = array(array('type' => 'submit', 'value' => $core->lang['save']));
			$core->form ('useredit', $action, $method, $title, $field, $button);

			$core->footer ();

		} else {

			// Company info
			$mans = $core->db->data( "SELECT * FROM ".DB_USER." WHERE user_comp = '".$core->user->comp."' ORDER BY user_name ASC" );
			$stores = $core->db->data( "SELECT * FROM ".DB_STORE." WHERE comp_id = '".$core->user->comp."'" );

			// Collect the store
			$offer = $core->wmsale->get( 'offers' );
			$price = $core->wmsale->get( 'price' );
			$store = array();
			foreach ( $stores as &$s ) {				if ( ! isset( $store[$s['offer_id']] ) ) $store[$s['offer_id']] = array( 'name' => $offer[$s['offer_id']], 'price' => $price[$s['offer_id']], 'count' => 0, 'vars' => array() );
				if ( $s['var_id'] ) {					$store[$s['offer_id']]['vars'][$s['var_id']] = array( 'count' => $s['store_count'] );
				} else $store[$s['offer_id']]['count'] = $s['store_count'];
			} unset ( $s, $stores );

			// Storage Variants
			foreach ( $store as $oid => $s ) if ( $s['vars'] ) {				$vars = $core->wmsale->get( 'vars', $oid );
				foreach ( $vars as $o ) {					$store[$oid]['vars'][$o['var_id']]['name'] = $o['var_name'];
					$store[$oid]['vars'][$o['var_id']]['price'] = $o['var_price'];
				}
			}

		    $core->mainline->add ( $core->lang['company'], $core->url('m', 'comp') );
		    $core->header ();

			$core->tpl->load( 'body', 'company' );

			$core->tpl->vars( 'body', $comp );
			$core->tpl->vars( 'body', array(
				'u_edit'		=> $core->url( 'm', 'comp-info' ),
				'u_store'		=> $core->url( 'm', 'comp-store' ),
				'u_income'		=> $core->url( 'm', 'comp-income' ),
				'u_update'		=> $core->url( 'm', 'comp' ) . '?update=1',
				'fio'			=> $core->lang['username'],
				'addr'			=> $core->lang['address'],
				'bank'			=> $core->lang['comp_bank'],
				'acc'			=> $core->lang['comp_acc'],
				'ks'			=> $core->lang['comp_ks'],
				'inn'			=> $core->lang['comp_inn'],
				'bik'			=> $core->lang['comp_bik'],
				'name'			=> $core->lang['name'],
				'phone'			=> $core->lang['phone'],
				'email'			=> $core->lang['email'],
				'price'			=> $core->lang['price'],
				'count'			=> $core->lang['count'],
				'action'		=> $core->lang['action'],
				'level'			=> $core->lang['level'],
				'edit'			=> $core->lang['edit'],
				'del'			=> $core->lang['del'],
				'confirm'		=> $core->lang['confirm'],
				'update'		=> $core->lang['update'],
				'store'			=> $core->lang['comp_store'],
				'income'		=> $core->lang['comp_store_in'],
				'sedit'			=> $core->lang['comp_store_edit'],
				'users'			=> $core->lang['comp_users'],
			));

			// Store show
			foreach ( $store as &$s ) {
            	$core->tpl->block( 'body', 'store', $s );
                foreach ( $s['vars'] as $v ) $core->tpl->block( 'body', 'store.var', $v );
			}

			// Managers
			$stats = company_stats( $core, (int) $core->get['update'] );
			$core->tpl->vars( 'body', $core->lang['comp_stats'] );
		    foreach ( $mans as &$i ) {
		        $core->tpl->block ( 'body', 'user', array (
		            'name'		=> $i['user_name'],
		            'email' 	=> $i['user_mail'],
		            'today'		=> (int) $stats['today'][$i['user_id']],
		            'yest'		=> (int) $stats['yest'][$i['user_id']],
		            'total'		=> (int) $stats['total'][$i['user_id']],
		            'cancel'	=> (int) $stats['cancel'][$i['user_id']],
		            'wait'		=> (int) $stats['wait'][$i['user_id']],
		            'pack'		=> (int) $stats['pack'][$i['user_id']],
		            'send'		=> (int) $stats['send'][$i['user_id']],
		            'done'		=> (int) $stats['done'][$i['user_id']],
		            'level'		=> $i['user_compad'] ? $core->lang['admin'] : $core->lang['user_works'][1],
		            'edit'		=> $core->url ( 'i', 'comp', $i['user_id'] ),
		            'del'		=> $core->url ( 'a', 'comp-del', $i['user_id'] ),
		        ));
		    } unset ( $i, $mans );

			$core->tpl->output( 'body' );

		    $title	= $core->lang['comp_user_add'];
		    $action	= $core->url ( 'a', 'comp-add', '' );
		    $method	= 'post';
		    $field 	= array(
	            array('type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['user_name'], 'descr' => $core->lang['user_name_d'] ),
	            array('type' => 'text', 'length' => 100, 'name' => 'email', 'head' => $core->lang['user_email'], 'descr' => $core->lang['user_email_d'] ),
	            array('type' => 'pass', 'length' => 32, 'name' => 'pass', 'head' => $core->lang['user_pass'], 'descr' => $core->lang['user_pass_d'] ),
		    );
		    $button = array(array('type' => 'submit', 'value' => $core->lang['save']));
		    $core->form ( 'useradd', $action, $method, $title, $field, $button );

			$core->footer ();

		}

		$core->_die();

	}

	return false;

}