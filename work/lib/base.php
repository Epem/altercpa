<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / base.php
 *  Description:	Basic profile functions
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

function base_menu ( $core, $menu ) {
	array_push( $menu, 'news' );
	return $menu;

}

function base_action ( $core ) {
	$action = ( $core->get['a'] ) ? $core->get['a'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );

	switch ( $action ) {

	  //
	  // Profile
	  //

	  case 'profile':

		// Basic Profile Data
		$data = array (
			'name'		=> $core->text->line( $core->post['name'] ),
			'wmr'		=> $core->text->line( $core->post['wmr'] ),
			'news'		=> $core->post['news'] ? 1 : 0,
		);
		if ( $data['name'] ) {

			// Profile Email
			$email = $core->text->email( $core->post['email'] );
			if ( $email && $email != $core->user->mail ) {
				$uid = $core->db->field( "SELECT user_id FROM ".DB_USER." WHERE user_mail = '$email' LIMIT 1" );
				if ( $uid && $uid == $core->user->id ) $data['mail'] = $email;
			}

			// Password
			if ( $core->post['pass'] && $core->post['pass'] == $core->post['conf'] ) {
				$data['pass'] = $core->text->pass( $core->post['pass'] );
			}

			// Saving
			$message = $core->user->edit( $data ) ? 'ok' : 'error';
			$core->go($core->url('mm', 'profile', $message ));

		} else $core->go($core->url('mm', 'profile', 'info' ));

	  case 'resetapi':

		$core->user->edit(array( 'api' => md5(microtime()) ));
	    $core->go($core->url('mm', 'profile', 'ok' ));

	  //
	  // Money
	  //

	  case 'out':

		if ( $core->user->wmr ) {
			$cash = (int) $core->post['cash'];
			require_once ( PATH_LIB . 'finance.php' );
	    	$f = new Finance( $core );
			if ( $cash >= 2000 && $cash <= $core->user->cash && $f->add( $core->user->id, 0, -$cash, 4, $core->user->wmr ) ) {
		        $core->go($core->url('mm', 'money', 'out-ok' ));
			} else $core->go($core->url('mm', 'money', 'out-e' ));
		} else $core->go($core->url('mm', 'money', 'out-w' ));

	  case 'cancel':

		$c = $core->db->row( "SELECT * FROM ".DB_CASH." WHERE cash_id = '$id' LIMIT 1" );
		if ( $c['user_id'] == $core->user->id && $c['cash_type'] == 4 ) {
			require_once ( PATH_LIB . 'finance.php' );
	    	$f = new Finance( $core );
			if ( $f->del( $id ) ) {
		        $core->go($core->url('mm', 'money', 'cancel-ok' ));
			} else $core->go($core->url('mm', 'money', 'cancel-e' ));
		} else $core->go($core->url('mm', '', 'access' ));

	  //
	  // Support
	  //

	  case 'suppa':
		require_once PATH_LIB . 'support.php';
		support_add( $core, $core->user->id, 0, $core->post['text'] );
		if ( $core->get['z'] == 'ajax' ) {
			echo 'ok'; $core->_die();
		} else $core->go($core->url( 'm', 'support' ));

	  case 'suppu':

		require_once PATH_LIB . 'support.php';
		$messages = support_show( $core, $core->user->id, 0, $core->get['from'] );

		if ( $mc = count( $messages ) ) {
			$core->tpl->load( 'body', 'message' );
			$mn = $mx = $mm = 0;
			foreach ( $messages as &$m ) {
				$core->tpl->block( 'body', 'msg', $m );
				$mx = max( $mx, $m['id'] );
				$mn = $mn ? min( $mn, $m['id'] ) : $m['id'];
                if ( $m['new'] ) $mm += 1;
			}
			$core->tpl->vars( 'body', array(
				'showmore'	=> $core->lang['support_more'],
				'mn'		=> $mn,
				'mx'		=> $mx,
				'mc'		=> $mm,
			));
			if ( $core->get['from'] >= 0 ) $core->tpl->block( 'body', 'more' );
			$core->tpl->output( 'body' );
		} $core->_die();

	}

	return false;

}

function base_module ( $core ) {
	$module	= ( $core->get['m'] ) ? $core->get['m'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );
	$page	= ( $core->get['page'] > 0 ) ? (int) $core->get['page'] : 1;
	$message = ( $core->get['message'] ) ? $core->get['message'] : null;

	switch ( $module ) {

	  case 'talk':

	    if ( $id ) {
			$n = $core->db->row( "SELECT * FROM ".DB_NEWS." WHERE news_id = '$id' LIMIT 1" );

		    $core->mainline->add ( $core->lang['news'], $core->url('m', 'news') );
		    $core->mainline->add ( $n['news_title'] );
		    $core->header ();

			$core->tpl->load( 'body', 'talk' );
			$core->tpl->vars( 'body', array(
				'id'		=> $n['news_id'],
				'url'		=> $core->url( 'i', 'talk', $n['news_id'] ),
				'title'		=> $n['news_title'],
				'text'		=> $core->text->out( $n['news_text'] ),
				'group'		=> $core->lang['news_groups'][$n['news_group']],
				'type'		=> $n['news_group'],
				'date'		=> smartdate( $n['news_time'] ),
				'disqus'	=> DISQUS,
				'base'		=> rtrim( BASEURL, '/' ),
			));
			$core->tpl->output( 'body' );

			$core->footer ();
			$core->_die();

	    }

	  case 'news':

		switch ( $message ) {
	    	case 'ok':	$core->info( 'info', 'done' ); break;
	    	case 'e':	$core->info( 'error', 'error' ); break;
		}

		switch ( $core->user->work ) {			case 1:		$in = '0,2';	break;
			case 2: 	$in = '0,1,2';	break;
			default:	$in = '0,1';
		}

		$vip = ( $core->user->vip ) ? '' : ' AND news_vip = 0';

		$sh = 10; $st = $sh * ( $page - 1 );
		$nc = $core->db->field( "SELECT COUNT(*) FROM ".DB_NEWS." WHERE news_group IN ( $in ) $vip" );
		$news = $core->db->data( "SELECT * FROM ".DB_NEWS." WHERE news_group IN ( $in ) $vip ORDER BY news_id DESC LIMIT $st, $sh" );

	    $core->mainline->add ( $core->lang['news'], $core->url('m', 'news') );
	    $core->header ();

		$core->tpl->load( 'body', 'news' );

	    $core->tpl->vars ('body', array (
	    	'u_add'			=> $core->url( 'm', 'news-add' ),
	    	'add'			=> $core->lang['news_add_h'],
			'pages'			=> pages ( $core->url( 'm', 'news' ), $nc, $sh, $page ),
			'shown'			=> sprintf( $core->lang['shown'], $st+1, min( $st+$sh, $nc ), $nc ),
			'edit'			=> $core->lang['edit'],
			'del'			=> $core->lang['del'],
			'disqus'		=> DISQUS,
	    ));

		if ( $core->user->level ) $core->tpl->block( 'body', 'add' );

		foreach ( $news as $n ) {			$core->tpl->block( 'body', 'news', array(
				'id'		=> $n['news_id'],
				'url'		=> $core->url( 'i', 'talk', $n['news_id'] ),
				'title'		=> $n['news_title'],
				'vip'		=> $n['news_vip'] ? $core->lang['iamvip'] : '',
				'text'		=> $core->text->out( $n['news_text'] ),
				'group'		=> $core->lang['news_groups'][$n['news_group']],
				'type'		=> $n['news_group'],
				'date'		=> smartdate( $n['news_time'] ),
				'edit'		=> $core->url( 'i', 'news', $n['news_id'] ),
				'del'		=> $core->url( 'a', 'news-del', $n['news_id'] ),
			));
			if ( $core->user->level ) $core->tpl->block( 'body', 'news.edit' );
		}

		$core->tpl->output( 'body' );

		$core->footer ();
		$core->_die();

	  // Profile
	  case 'profile':

		switch ( $message ) {
	    	case 'ok':		$core->info( 'info', 'done_profile' ); break;
	    	case 'error':	$core->info( 'error', 'error_profile' ); break;
	    	case 'info':	$core->info( 'error', 'info_profile' ); break;
		}

	    $core->mainline->add ( $core->lang['profile'], $core->url('m', 'profile') );
	    $core->header ();

	    $title	= $core->lang['profile_h'];
	    $action	= $core->url ( 'a', 'profile', 0 );
	    $method	= 'post';
	    $field 	= array(
	    	array('type' => 'line', 'value' => $core->text->lines( $core->lang['profile_t'] ) ),
			array('type' => 'head', 'value' => $core->lang['user_info'] ),
			array('type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['user_name'], 'descr' => $core->lang['user_name_d'], 'value' => $core->user->name ),
			array('type' => 'text', 'length' => 100, 'name' => 'email', 'head' => $core->lang['user_email'], 'descr' => $core->lang['user_email_d'], 'value' => $core->user->email ),
			array('type' => 'checkbox', 'name' => 'news', 'head' => $core->lang['user_news'], 'descr' => $core->lang['user_news_d'], 'checked' => $core->user->news ),
			array('type' => 'text', 'length' => 25, 'name' => 'wmr', 'head' => $core->lang['user_wmr'], 'descr' => $core->lang['user_wmr_d'], 'value' => $core->user->wmr ),
			array('type' => 'head', 'value' => $core->lang['user_access'] ),
			array('type' => 'pass', 'length' => 32, 'name' => 'pass', 'head' => $core->lang['user_pass'], 'descr' => $core->lang['user_pass_d'] ),
			array('type' => 'pass', 'length' => 32, 'name' => 'conf', 'head' => $core->lang['user_conf'], 'descr' => $core->lang['user_conf_d'] ),
	    );

		$field[] = array('type' => 'head', 'value'	=> $core->lang['user_apis'] );
    	$field[] = array('type' => 'line', 'value' => $core->text->lines( $core->lang['user_apis_t'] ) );
		$field[] = array('type' => 'text', 'name' => 'api_id" readonly="readonly', 'head' => $core->lang['user_api'], 'descr' => $core->lang['user_api_d'], 'value' => $core->user->id );
		$field[] = array('type' => 'text', 'name' => 'api_key" readonly="readonly', 'head' => $core->lang['user_key'], 'descr' => sprintf( $core->lang['user_key_d'], $core->url( 'a', 'resetapi', 0 ) ), 'value' => $core->user->api );

	    $button = array( array('type' => 'submit', 'value' => $core->lang['save']) );
	    $core->form ( 'profile', $action, $method, $title, $field, $button );

		$core->footer ();
		$core->_die();

	  // Finance
	  case 'money':

		switch ( $message ) {
	    	case 'out-ok':	$core->info( 'info', 'done_fin_out' ); break;
	    	case 'pay-ok':	$core->info( 'info', 'done_fin_pay' ); break;
	    	case 'out-e':	$core->info( 'error', 'error_fin_out' ); break;
	    	case 'out-w':	$core->info( 'error', 'error_fin_wmr' ); break;
	    	case 'pay-e':	$core->info( 'error', 'error_fin_pay' ); break;
		}

	    $page   = ($core->get['page']) ? (int) $core->get['page'] : 1; $en = 25; $st = $en * ($page-1);
	   	$trc 	= $core->db->field ( "SELECT COUNT(*) FROM ".DB_CASH." WHERE user_id = '".$core->user->id."'");
	   	$trs 	= $trc ? $core->db->data ( "SELECT * FROM ".DB_CASH." WHERE user_id = '".$core->user->id."' ORDER BY cash_time DESC LIMIT $st, $en") : array();

		$add = -$core->user->cash;
		if ( $add < 0 ) $add = '';

		$core->mainline->add( $core->lang['finance_h'], $core->url( 'm', 'money' ) );
	    $core->header ();

		$core->tpl->load( 'body', 'finance' );

	    $core->tpl->vars ('body', array (
			'title'			=> $core->lang['finance_h'],
			'text'			=> $core->text->lines( $core->lang['finance_t'] ),
			'u_add'			=> '',
			'pay'			=> $core->lang['finance_pay'],
			'toadd'			=> $add,
			'pay_id'		=> $core->user->id,
			'pay_comment'	=> base64_encode(sprintf( $core->lang['pay_comment'], $core->user->id )),
			'pay_purse'		=> WMR,
			'u_out'			=> $core->url( 'a', 'out', 0 ),
			'toout'			=> ( $core->user->cash < 1000 ) ? '' : $core->user->cash,
			'out'			=> $core->lang['finance_out'],
			'nofins'		=> $core->lang['nofins'],
			'type'			=> $core->lang['type'],
			'cash'			=> $core->lang['cash'],
			'status'		=> $core->lang['status'],
			'date'			=> $core->lang['date'],
			'action'		=> $core->lang['action'],
			'cancel'		=> $core->lang['cancel'],
			'confirm'		=> $core->lang['confirm'],
			'pages'			=> pages ($core->url('m', 'money'), $trc, $en, $page),
	    ));

		if ( count( $trs ) ) {
			foreach ( $trs as &$c ) {
				$core->tpl->block( 'body', 'fin', array(
					'type'		=> $core->lang['cash_type'][$c['cash_type']],
					'tid'		=> $c['cash_type'],
					'descr'		=> $c['cash_descr'] ? '('.$c['cash_descr'].')' : '',
					'value'		=> rur( $c['cash_value'] ),
		            'cancel'	=> $core->url ('a', 'cancel', $c['cash_id'] ),
		            'time'		=> smartdate( $c['cash_time'] ),
				));
				if ( $c['cash_type'] == 4 ) $core->tpl->block( 'body', 'fin.action' );
			} unset ( $t, $trs );
		} else $core->tpl->block( 'body', 'nofin', array() );

		if ( $core->user->work == 1 ) $core->tpl->block( 'body', 'canin' );

		$core->tpl->output( 'body' );

	    $core->footer ();
	    $core->_die();

	  case 'support':

		require_once PATH_LIB . 'support.php';

		$core->mainline->add( $core->lang['support'], $core->url( 'm', 'support' ) );
	    $core->header ();

		$core->tpl->load( 'body', 'message' );

		$core->tpl->vars( 'body', array(
        	'title'			=> $core->lang['support'],
        	'nomessage1'	=> $core->lang['support_nm1'],
        	'nomessage2'	=> $core->lang['support_nm2'],
			'add'			=> $core->lang['send'],
			'showmore'		=> $core->lang['support_more'],
			'placeholder'	=> $core->lang['support_placeholder'],
			'u_load'		=> $core->url( 'a', 'suppu', '' ),
			'u_add'			=> $core->url( 'a', 'suppa', '' ),
			'mc' 			=> 0,
        ));

		$core->tpl->block( 'body', 'face' );

		$mn = $mx = 0;
		$messages = support_show( $core, $core->user->id, 0, 0 );
		if ( $mc = count( $messages ) ) {
			foreach ( $messages as &$m ) {
				$core->tpl->block( 'body', 'msg', $m );
				$mx = max( $mx, $m['id'] );
				$mn = $mn ? min( $mn, $m['id'] ) : $m['id'];
			} unset ( $m );
			$core->tpl->block( 'body', 'more' );
		} else $core->tpl->block( 'body', 'face.nomsg' );
		$core->tpl->vars( 'body', array( 'mn' => $mn, 'mx' => $mx ));

		$core->tpl->output( 'body' );

	    $core->footer ();
	    $core->_die();

	}

	return false;

}

function base_404 ( $core ) {
	$core->mainline->add( '404' );
	$core->header();
	$core->tpl->load( 'body', '404' );
	$core->tpl->output( 'body' );
	$core->footer();
	$core->_die();

}