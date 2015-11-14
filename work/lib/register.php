<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / register.php
 *  Description:	Home page and registration processing
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

// Register Action
if ( $core->post['register'] ) {

   	$email	= $core->text->email( $core->post['email'] );
   	$name	= $core->text->line( $core->post['name'] );
   	$pass	= $core->text->pass( $core->post['pass'] );
   	$ptxt	= $core->text->line( $core->post['pass'] );
    $ref	= (int) $core->post['ref'];

	if ( $ref ) {		$rd = $core->user->get( $ref );
		if ( $rd['user_id'] ) {			if ( ! $rd['user_sub'] ) {				$sub = ( $core->user->get( $rd['user_ref'], 'user_work' ) == -2 ) ? $rd['user_ref'] : 0;
			} else $sub = $rd['user_sub'];
		} else $ref = $sub = 0;
	} else $sub = 0;

	$u = $core->db->field( "SELECT user_id FROM ".DB_USER." WHERE user_mail = '$email' LIMIT 1" );
	if ( !$u && $email ) {
		if ( strlen( $ptxt ) >= 4  ) {
			if ( strlen( $name ) >= 4 ) {
				$sql = "INSERT INTO ".DB_USER." SET user_name = '$name', user_mail = '$email', user_pass = '$pass', user_api = '".md5(microtime())."', user_ref = '$ref', user_sub = '$sub'";
				if ( $core->db->query( $sql ) ) {
					$core->post['in_user'] = $email;
					$core->post['in_pass'] = $ptxt;
					$core->user->login( $core );
					$core->go($core->url( 'm', 'profile' ));
				} else $re = 4;
			} else $re = 3;
		} else $re = 2;
	} else $re = 1;

} else $re = 0;

if ( $core->post['recover'] ) {

	$email = $core->text->email( $core->post['recover'] );
	$u = $email ? $core->db->row( "SELECT * FROM ".DB_USER." WHERE user_mail = '$email' LIMIT 1" ) : null;
	if ( $u['user_id'] ) {
		$key = $u['user_id'] . md5(crypto::encode( $u['user_mail'] . $u['user_pass'], $core->crypto ));
		$core->email->send( $u['user_mail'], $core->lang['mail_recover_h'], sprintf( $core->lang['mail_recover_t'], $key ) );
		$core->go( '/?recovered' );
	}

} elseif ( $core->get['recoverpass'] ) $core->go( '/' );

$core->tpl->load ( 'login', 'login' );

if ( $ref = (int) $core->get['from'] ) { 	$core->session_set( 'ref', $ref );
	$url = ( $core->sercer['HTTPS'] ? 'https://' : 'http://' ) . $core->server['HTTP_HOST'];
	$core->go( $url );
} else $ref = $core->session_get( 'ref' );

if ( $core->server['REQUEST_URI'] != '/' ) $core->go( '/' );

$core->tpl->vars ('login', array(
	'site'		=> $core->lang['site_name'],
	'login'		=> $core->lang['login_page'],
	'enter'		=> $core->lang['login_enter'],
	'reg'		=> $core->lang['reg_page'],
	'register'	=> $core->lang['reg_do'],
	'user'		=> $core->lang['login_user'],
	'mail'		=> $core->lang['login_mail'],
	'pass'		=> $core->lang['login_pass'],
	're'		=> $core->lang['reg_error'][$re],
	'forgot'	=> $core->lang['forgot_pass'],
	'recover'	=> $core->lang['forgot_recover'],
	'formail'	=> $core->lang['forgot_email'],
	'cancel'	=> $core->lang['cancel'],
	'ref'		=> $ref,
));

if ( $re ) {
	$core->tpl->block( 'login', 'error', array() );
	$core->tpl->vars( 'login', array( 'er'.$re => 'bad' ) );
}

$core->tpl->output ( 'login' );
$core->_die ();