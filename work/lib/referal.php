<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / referal.php
 *  Description:	Referal control panel
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

function referal_menu ( $core, $menu ) {

	array_push( $menu, 'referal', 'lead', 'outs' );
	return $menu;

}

function referal_action ( $core ) {
	$action = ( $core->get['a'] ) ? $core->get['a'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );

	switch ( $action ) {
	  case 'ref-add':

		$name 	= $core->text->line ( $core->post['name'] );
		$email	= $core->text->email ( $core->post['email'] );
		$pass	= $core->text->pass ( trim( $core->post['pass'] ) );

		$uid = $core->db->field( "SELECT user_id FROM ".DB_USER." WHERE user_mail = '$email' LIMIT 1" );
		if ( ! $uid ) {

		    $sql = "INSERT INTO ".DB_USER." SET user_name = '$name', user_mail = '$email', user_pass = '$pass', user_work = 0, user_ref = '".$core->user->id."'";
		    if ( $name && $email && trim($core->post['pass']) && $core->db->query( $sql ) ) {
		        $core->go($core->url( 'mm', 'referal', 'add-ok' ));
		    } else $core->go($core->url( 'mm', 'referal', 'add-e' ));

		} else $core->go($core->url( 'mm', 'referal', 'exists' ));

	  case 'ref-edit':

		$user = $core->db->row( "SELECT * FROM ".DB_USER." WHERE user_id = '$id' LIMIT 1" );
		if ( $user['user_ref'] && $user['user_ref'] == $core->user->id ) {

			$data = array(
				'user_name' 	=> $core->text->line ( $core->post['name'] ),
				'user_mail'		=> $core->text->email ( $core->post['email'] ),
				'user_vip'		=> $core->post['vip'] ? 1 : 0,
				'user_ban'		=> $core->post['ban'] ? 1 : 0,
				'user_warn'		=> $core->post['warn'] ? 1 : 0,
			);
			if (trim( $core->post['pass'] )) $data['user_pass'] = $core->text->pass( $core->post['pass'] );

			if ( $data['user_mail'] != $user['user_mail'] ) {
				$uid = $core->db->field( "SELECT user_id FROM ".DB_USER." WHERE user_mail = '".$data['user_mail']."' LIMIT 1" );
				if ( $uid ) $core->go($core->url( 'mm', 'referal', 'edit-e' ));
			}

		    if ( $core->user->set ( $id, $data ) ) {
				$core->go($core->url( 'mm', 'referal', 'edit-ok' ));
		    } else $core->go($core->url( 'mm', 'referal', 'edit-e' ));

		} else $core->go($core->url( 'mm', 'referal', 'access' ));

	  //
	  // Outputs
	  //

	  case 'out-accept':

		$c = $core->db->row( "SELECT * FROM ".DB_CASH." WHERE cash_id = '$id' LIMIT 1" );
		if ( $c['cash_type'] == 4 ) {
			$ur = $core->user->get( $c['user_id'], 'user_ref' );
			if ( $ur == $core->user->id ) {
				require_once ( PATH_LIB . 'finance.php' );
		    	$f = new Finance( $core );
		    	if ( $f->edit( $id, 5 ) ) {
		        	$core->go($core->url( 'mm', 'outs', 'acc-ok' ));
		    	} else $core->go($core->url( 'mm', 'outs', 'acc-e' ));
	    	} else $core->go($core->url( 'mm', 'outs', 'acc-e' ));
		} else $core->go($core->url( 'mm', 'outs', 'acc-e' ));

	  case 'out-decline':

		$c = $core->db->row( "SELECT * FROM ".DB_CASH." WHERE cash_id = '$id' LIMIT 1" );
		if ( $c['cash_type'] == 4 ) {
			$ur = $core->user->get( $c['user_id'], 'user_ref' );
			if ( $ur == $core->user->id ) {
				require_once ( PATH_LIB . 'finance.php' );
		    	$f = new Finance( $core );
		    	if ( $f->del( $id ) ) {
		        	$core->go($core->url( 'mm', 'outs', 'dec-ok' ));
		    	} else $core->go($core->url( 'mm', 'outs', 'dec-e' ));
	    	} else $core->go($core->url( 'mm', 'outs', 'dec-e' ));
		} else $core->go($core->url( 'mm', 'outs', 'dec-e' ));

	  case 'out-bulk':

		$outs = array(); foreach ( $core->post['ids'] as $i ) if ( $i = (int) $i ) $outs[] = $i;
		$ul = $core->db->col( "SELECT user_id FROM ".DB_USER." WHERE user_ref = '".$core->user->id."'" );
		$otp = $core->db->col( "SELECT cash_id FROM ".DB_CASH." WHERE cash_id IN ( ".implode( ',', $outs )." ) AND user_id IN ( ".implode( ',', $ul )." ) AND cash_type = 4" );

		require_once ( PATH_LIB . 'finance.php' );
    	$f = new Finance( $core );

		if ( $core->post['decline'] ) {
        	foreach ( $otp as $id ) $f->del( $id );
		} else foreach ( $otp as $id ) $f->edit( $id, 5 );

		$core->go($core->url( 'mm', 'outs', 'ok' ));

	}

}

function referal_module ( $core ) {
	$module	= ( $core->get['m'] ) ? $core->get['m'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );
	$page	= ( $core->get['page'] > 0 ) ? (int) $core->get['page'] : 1;
	$message = ( $core->get['message'] ) ? $core->get['message'] : null;

	switch ( $module ) {
	  case 'outs':

		switch ( $message ) {
	    	case 'acc-ok':	$core->info( 'info', 'done_out_acc' ); break;
	    	case 'dec-ok':	$core->info( 'info', 'done_out_dec' ); break;
	    	case 'acc-e':	$core->info( 'error', 'error_out_acc' ); break;
	    	case 'dec-e':	$core->info( 'error', 'error_out_dec' ); break;
		}

		$u = $core->db->icol( "SELECT user_id, user_name FROM ".DB_USER." WHERE user_ref = '".$core->user->id."'" );
	   	$trs = $core->db->data ( "SELECT * FROM ".DB_CASH." WHERE cash_type = 4 AND user_id IN ( ".implode( ',', array_keys($u) )." ) ORDER BY user_id ASC, cash_time DESC");
		if ( count( $trs ) ) {
			$ui = $s = array();
			foreach ( $trs as &$t ) {
				$ui[] = $t['user_id'];
				$s[$t['user_id']] += $t['cash_value'];
			} unset ( $t );
			$ui = implode( ',', array_unique( $ui ) );
			$bo = $core->db->icol( "SELECT wm_id, COUNT(*) FROM ".DB_ORDER." WHERE wm_id IN ( $ui ) AND order_check = 1 GROUP BY wm_id" );
		} else $u = $bo = $s = array();

		$core->mainline->add( $core->lang['menu_outs'], $core->url( 'm', 'outs' ) );
	    $core->header ();

		$core->tpl->load( 'body', 'outs' );

	    $core->tpl->vars ('body', array (
			'user'			=> $core->lang['user'],
			'accept'		=> $core->lang['do'],
			'decline'		=> $core->lang['decline'],
			'cash'			=> $core->lang['cash'],
			'pay'			=> $core->lang['pay'],
			'time'			=> $core->lang['date'],
			'action'		=> $core->lang['action'],
			'cancel'		=> $core->lang['cancel'],
			'confirma'		=> $core->lang['oconfirma'],
			'confirmd'		=> $core->lang['oconfirmd'],
			'nofins'		=> $core->lang['noout'],
			'u_bulk'		=> $core->url( 'a', 'out-bulk', 0 ),
	    ));

		if ( count( $trs ) ) {
			$ou = 0;
			foreach ( $trs as &$c ) {

				if ( $ou != $c['user_id'] ) {
					$ou = $c['user_id'];
					$core->tpl->block( 'body', 'user', array(
						'id'		=> $ou,
						'user'		=> $u[$ou],
						'orders'	=> $bo[$ou],
						'uu'		=> $core->url( 'i', 'users', $ou ),
						'value'		=> sprintf( "%0.2f", abs($s[$ou]) ),
					));
					if ( $bo[$ou] ) $core->tpl->block( 'body', 'user.bad' );
				}

				$core->tpl->block( 'body', 'user.fin', array(
					'id'		=> $c['cash_id'],
					'wmr'		=> $c['cash_descr'],
					'value'		=> sprintf( "%0.2f", abs($c['cash_value']) ),
		            'accept'	=> $core->url ('a', 'out-accept', $c['cash_id'] ),
		            'decline'	=> $core->url ('a', 'out-decline', $c['cash_id'] ),
		            'time'		=> smartdate( $c['cash_time'] ),
				));

			} unset ( $t, $trs );
		} else $core->tpl->block( 'body', 'nofin', array() );

		$core->tpl->output( 'body' );

	    $core->footer ();
	  	$core->_die ();

	  case 'lead':

		$uids = $core->db->col( "SELECT user_id FROM ".DB_USER." WHERE user_ref = '".$core->user->id."' OR user_sub = '".$core->user->id."'" );
	  	if ( ($wm = (int) $core->get['wm']) && in_array( $wm, $uids ) ) {
        	$where = array( "wm_id = '$wm'" );
	  	} else $where = array( "wm_id IN ( ".implode( ',', $uids )." )" );

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
			switch ( $s ) {
				case 'w':	$where[] = " order_webstat < 5 "; break;
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

		$core->tpl->load( 'body', 'leads' );

		$core->tpl->vars( 'body', array(
			'nostats'		=> $core->lang['nostats'],
			'date'			=> $core->lang['date'],
			'flow'			=> $core->lang['flow'],
			'offer'			=> $core->lang['offer'],
			'status'		=> $core->lang['status'],
			'show'			=> $core->lang['show'],
			'site'			=> $core->lang['site'],
			'user'			=> $core->lang['user'],
			'calls'			=> $core->lang['order_calls_sh'],
			'reason'		=> $core->lang['order_reason'],
			'd'				=> $d,
			'u_stat'		=> $core->url( 'm', 'stats' ),
			'stat'			=> $core->lang['stats_date'],
			'pages'			=> pages ( $core->url( 'm', 'lead?' ) . ( $wm ? 'wm='.$wm.'&' : '' ) . ( $d ? 'd='.$d.'&' : '' ) . ( $s ? 's='.$s.'&' : '' ) . ( $o ? 'o='.$o : '' ) . ( $w ? 'w='.$w : '' ), $orders, $sh, $page ),
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

		if ( $orders ) foreach ( $order as $r ) {

			$uid = $r['wm_id'];
			$user = $uid ? $core->user->get( $uid ) : array();

			$core->tpl->block( 'body', 'order', array(
				'offer'			=> $offer[$r['offer_id']],
				'site'			=> $site[$r['site_id']],
				'uid'			=> (strlen($r['ext_uid'])>25) ? sprintf( '<input type="text" value="%s" class="intable-view" />', htmlspecialchars($r['ext_uid']) ) : $r['ext_uid'],
				'src'			=> $r['ext_src'],
				'ip'			=> int2ip( $r['order_ip'] ),
				'country'		=> $r['order_country'],
				'time'			=> smartdate( $r['order_time'] ),
				'stid'			=> ( $r['order_webstat'] < 6 || $r['order_webstat'] == 12 ) ? $r['order_webstat'] : 10,
				'status'		=> ( $r['order_webstat'] < 6 || $r['order_webstat'] == 12 ) ? $core->lang['statuso'][$r['order_webstat']] : $core->lang['statusok'],
				'calls'			=> $r['order_calls'],
				'reason'		=> $r['order_reason'] ? $core->lang['reasono'][$r['order_reason']] : ( ( $r['order_webstat'] == 5 || $r['order_webstat'] == 12 ) ? $core->lang['noreason'] : ( $r['order_check'] ? $core->lang['stat_check'] : '' )  ),
				'uid'			=> $uid,
				'uname'			=> $uid ? ( $user['user_level'] ? '<b>'.$user['user_name'].'</b>' : $user['user_name'] ) : $core->lang['order_src_sh'],
				'uclass'		=> $r['order_check'] ? 'warn' : ( $uid ? ( $r['ext_id'] ? 'ext' : ( $user['user_vip'] ? 'vip' : 'user' ) ) : 'search' ),
			));

		} else $core->tpl->block( 'body', 'nostat' );

		$core->tpl->output( 'body' );

		$core->footer();
	  	$core->_die();

	  case 'referal':
	  default:

		switch ( $message ) {
	    	case 'add-ok':	$core->info( 'info', 'done_user_add' ); break;
	    	case 'edit-ok':	$core->info( 'info', 'done_user_edit' ); break;
	    	case 'add-e':	$core->info( 'error', 'error_user_add' ); break;
	    	case 'edit-e':	$core->info( 'error', 'error_user_edit' ); break;
		}

	  	if ( $id ) {

	    	$user = $core->db->row ( "SELECT * FROM ".DB_USER." WHERE user_id = '$id' LIMIT 1" );

		    $core->mainline->add ( $core->lang['admin_user_h'], $core->url('m', 'users') );
		    $core->mainline->add ( $user['user_name'] );
		    $core->header ();

		    $title	= $core->lang['user_edit'];
		    $action	= $core->url ( 'a', 'ref-edit', $id );
		    $method	= 'post';
		    $field 	= array(
	            array( 'type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['user_name'], 'descr' => $core->lang['user_name_d'], 'value' => $user['user_name']),
	            array( 'type' => 'text', 'length' => 100, 'name' => 'email', 'head' => $core->lang['user_email'], 'descr' => $core->lang['user_email_d'], 'value' => $user['user_mail']),
	            array( 'type' => 'text', 'length' => 32, 'name' => 'pass', 'head' => $core->lang['user_pass'], 'descr' => $core->lang['user_pass_d'] ),
	            array( 'type' => 'checkbox', 'name' => 'ban', 'head' => $core->lang['user_ban'], 'descr' => $core->lang['user_ban_d'], 'checked' => $user['user_ban'] ),
	            array( 'type' => 'checkbox', 'name' => 'warn', 'head' => $core->lang['user_warn'], 'descr' => $core->lang['user_warn_d'], 'checked' => $user['user_warn'] ),
	            array( 'type' => 'checkbox', 'name' => 'vip', 'head' => $core->lang['comp_vip'], 'descr' => $core->lang['comp_vip_d'], 'checked' => $user['user_vip'] ),
		    );
		    $button = array(array('type' => 'submit', 'value' => $core->lang['save']));
		    $core->form ('useredit', $action, $method, $title, $field, $button);

	        $core->footer ();

	    } else {

			$where = array( "( user_ref = '".$core->user->id."' OR user_sub = '".$core->user->id."' )" );

			if ( isset( $core->get['s'] ) && $core->get['s'] ) {
				require_once PATH_CORE . 'search.php';
				$search = new SearchWords( $core->get['s'] );
				if ( $s = $search->get() ) {
					$where[] = $search->field(array( 'user_name', 'user_mail' ));
				} else $s = false;
			} else $s = false;

			$where = implode( ' AND ', $where );
		    $sh = 30; $st = $sh * ( $page - 1 );
	    	$users	= $core->db->field ( "SELECT COUNT(*) FROM ".DB_USER." WHERE $where");
	    	$user	= $users ? $core->db->data ( "SELECT * FROM ".DB_USER." WHERE $where ORDER BY user_name ASC LIMIT $st, $sh" ) : array();

		    $core->mainline->add ( $core->lang['admin_user_h'], $core->url('m', 'referal') );
		    $core->header ();

		    $core->tpl->load( 'body', 'referals' );

		    $core->tpl->vars ('body', array (
		        'title'		    => $core->lang['admin_user_h'],
	            'text'			=> $core->text->lines ($core->lang['admin_user_t']),
		        'name'  	    => $core->lang['user'],
		        'email'  	    => $core->lang['email'],
		        'vip'  	   		=> $core->lang['iamvip'],
		        'level'  	    => $core->lang['level'],
		        'comp'  	    => $core->lang['company'],
		        'name'  	    => $core->lang['user'],
		        'info'			=> $core->lang['cash'],
		        'action'    	=> $core->lang['action'],
	            'enter'			=> $core->lang['enter'],
	            'edit'			=> $core->lang['edit'],
	            'del'			=> $core->lang['del'],
	            'confirm'		=> $core->lang['confirm'],
	            's'				=> $s,
	            'pages'			=> pages ( $core->url('m', 'referal?') . ( $s ? 's=' . $s : '' ), $users, $sh, $page ),
	            'shown'			=> sprintf( $core->lang['shown'], $st+1, min( $st+$sh, $users ), $users ),
				'search'		=> $core->lang['search'],
				'find'			=> $core->lang['find'],
		    ));

		    foreach ( $user as &$i ) {
    			$key = $i['user_id'] . md5(crypto::encode( $i['user_mail'] . $i['user_pass'], $core->crypto ));
		        $core->tpl->block ('body', 'user', array (
		            'id'        => $i['user_id'],
		            'name'		=> $search ? $search->highlight( $i['user_name'] ) : $i['user_name'],
		            'email'		=> $search ? $search->highlight( $i['user_mail'] ) : $i['user_mail'],
		            'mailto'	=> $i['user_mail'],
		            'vip'		=> $i['user_vip'] ? $core->lang['iamvip'] : '',
		            'icon'		=> $i['user_ban'] ? 'block' : ( $i['user_warn'] ? 'isua' : 'isok' ),
		            'ref'		=> $i['user_sub'] ? $core->user->get( $i['user_ref'], 'user_name' ) : '',
		            'cash'      => rur( $i['user_sub'] ? $i['user_sup'] : $i['user_got'] ),
		            'flw'		=> (int) $i['user_flw'],
		            'flwa'		=> (int) $i['user_flwa'],
		            'cr'		=> ( $i['user_cr'] < 10 ) ? sprintf( "%0.2f", $i['user_cr'] ) : sprintf( "%0.1f", $i['user_cr'] ),
		            'crc'		=> ( $i['user_cr'] < 10 ) ? ( ($i['user_cr'] < 5) ? 'green' : 'yellow' ) : ( ($i['user_cr'] > 20) ? 'red fat' : 'red' ),
		            'epc'		=> rur( $i['user_epc'] ),
		            'url'		=> $core->url ('m', 'lead' ) . '?wm=' . $i['user_id'],
		            'edit'      => $core->url ('i', 'referal', $i['user_id'] ),
		            'ip'		=> $i['user_ip'] ? int2ip( $i['user_ip'] ) : '',
		            'date'		=> $i['user_date'] ? date2form( $i['user_date'] ) : '',
		            'enter'		=> sprintf( $core->lang['mail_recover_r'], $key ),
		        ));
		    } unset ($d);

			$core->tpl->output ('body');

		    $title	= $core->lang['user_add'];
		    $action	= $core->url ( 'a', 'ref-add', 0 );
		    $method	= 'post';
		    $field 	= array(
	            array('type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['user_name'], 'descr' => $core->lang['user_name_d'] ),
	            array('type' => 'text', 'length' => 100, 'name' => 'email', 'head' => $core->lang['user_email'], 'descr' => $core->lang['user_email_d'] ),
	            array('type' => 'pass', 'length' => 32, 'name' => 'pass', 'head' => $core->lang['user_pass'], 'descr' => $core->lang['user_pass_d'] ),
		    );
		    $button = array(array('type' => 'submit', 'value' => $core->lang['save']));
		    $core->form ( 'useradd', $action, $method, $title, $field, $button );

	        $core->footer ('admin');

	    } $core->_die();

  	}

}