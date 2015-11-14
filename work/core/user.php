<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / user.php
 *  Description:	User data
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

class User { // class sitecache start

	// Cache Data Variables
    private		$core;

	public		$auth		= false;
    public		$id			= 0;
    public 		$name		= '';
    public		$email		= '';
    public		$level		= 0;
    public		$wmr		= 0;
    public		$cash		= 0;
    public		$work		= 0;
    public		$comp		= 0;
    public		$compad		= 0;
    public		$conv		= 0;
    public		$draw		= 0;

    // Constructor
    // For Simple User Login
	public function __construct ( $core ) {

		$this->core = $core;

		//
        // Authentication
		$this->logout ( $core );
		$this->login ( $core );
        //

        return true;

	}

	public function login( $core ) {
		// Loading Logged In User
	    if ($core->session_get('login_key') && $core->session_get('login_ssid')) {

	        $user_mail = crypto::decode ( $core->session_get('login_key'), $core->crypto );
	        $user_mail = $core->text->email ( $user_mail );

			$userdata = $this->getbyname( $user_mail );
	        if ( $userdata['user_id'] ) {
	            $set_ssid = md5 ($core->session_get('login_key') . crypto::encode ($userdata['user_pass'], $core->crypto));
	            if ($set_ssid != $core->session_get('login_ssid'))  $userdata = null;
	        } else $userdata = null;

	    } else $userdata = null;

        // Trying To Log In
        if ( $core->post['in_user'] && $core->post['in_pass'] ) {

	        $in_user = $core->text->email ( $core->post['in_user'] );
        	$in_pass = $core->text->pass  ( $core->post['in_pass'] );

			$userdata = $this->getbyname( $in_user );
            if ( $userdata['user_id'] && $in_pass == $userdata['user_pass'] ) {
	            $set_login  = crypto::encode ( $userdata['user_mail'], $core->crypto );
	            $set_ssid   = md5 ($set_login . crypto::encode ($userdata['user_pass'], $core->crypto));
	            $core->session_set ( 'login_key', $set_login );
	            $core->session_set ( 'login_ssid', $set_ssid );
            } else $userdata = null;

        }

        // Password Recovery
        if ( $core->get['recoverpass'] ) {

        	$token = $core->text->link( $core->get['recoverpass'] );
			$id = substr( $token, 0, -32 );
			$key = substr( $token, strlen( $id ) );
			$id = (int) $id;

			$userdata = $id ? $this->get( $id ) : null;
            if ( $userdata['user_id'] && $key == md5(crypto::encode( $userdata['user_mail'] . $userdata['user_pass'], $core->crypto )) ) {
	            $set_login  = crypto::encode ( $userdata['user_mail'], $core->crypto );
	            $set_ssid   = md5( $set_login . crypto::encode ($userdata['user_pass'], $core->crypto) );
	            $core->session_set ( 'login_key', $set_login );
	            $core->session_set ( 'login_ssid', $set_ssid );
            } else $userdata = null;

        }

        if ( $userdata ) {

			$this->id		= (int) $userdata['user_id'];
			$this->name		= $userdata['user_name'];
			$this->email	= $userdata['user_mail'];
			$this->level	= $userdata['user_level'];
			$this->work		= $userdata['user_work'];
			$this->ban		= $userdata['user_ban'];
			$this->cash		= $userdata['user_cash'];
			$this->wmr		= $userdata['user_wmr'];
			$this->ref		= $userdata['user_ref'];
			$this->sub		= $userdata['user_sub'];
			$this->ext		= $userdata['user_ext'];
			$this->comp		= $userdata['user_comp'];
			$this->compad	= $userdata['user_compad'];
			$this->call		= $userdata['user_call'];
			$this->shave	= $userdata['user_shave'];
			$this->vip		= $userdata['user_vip'];
			$this->api		= $userdata['user_api'];
			$this->news		= $userdata['user_news'];
			$this->supp_new	= $userdata['supp_new'];
            $this->auth		= true;

            $ip = ip2int( $core->server['REMOTE_ADDR'] );
            $dd = date( 'Ymd' );
            if ( $ip != $userdata['user_ip'] || $dd != $userdata['user_date'] ) {
             	$this->set( $userdata['user_id'], array( 'user_ip' => $ip, 'user_date' => $dd ) );
            }

        } else $this->auth = false;

	}

	public function logout ( $core ) {
        if ( $core->get['logout'] ) {
			$core->session_set ( 'login_key', null );
			$core->session_set ( 'login_ssid', null );
        }

	}

	public function get ( $id, $field = false ) {
		$name = 'user' . $id;
		$cache = $this->core->cache->user;
        if ( ! isset( $cache->{$name} ) ) {			$cache->{$name} = $data = $this->core->db->row( "SELECT * FROM ".DB_USER." WHERE user_id = '$id' LIMIT 1" );
        	$ename = 'mail' . md5( $data['user_mail'] );
        	$cache->{$ename} = $data;
        } else $data = $cache->{$name};

		return $field ? $data[$field] : $data;

	}

    public function set ( $id, $data ) {

		$oldmail = $this->get( $id, 'user_mail' );
		$cache = $this->core->cache->user;
		if ( $this->core->db->edit( DB_USER, $data, "user_id = '$id'" ) ) {			$cache->clear( 'user' . $id );
			$cache->clear( 'mail' . md5( $oldmail ) );
			if ( $data['user_mail'] && $data['user_mail'] != $oldmail ) $cache->clear( 'mail' . md5( $data['user_mail'] ) );
			return true;
		} else return false;

    }

    public function reset ( $id ) {
		$cache = $this->core->cache->user;
		$oldmail = $this->get( $id, 'user_mail' );
		$cache->clear( 'user' . $id );
		$cache->clear( 'mail' . md5( $oldmail ) );

    }

	public function getbyname ( $email, $field = false ) {
		$name = 'mail' . md5( $email );
		$cache = $this->core->cache->user;
        if ( ! isset( $cache->{$name} ) ) {
			$cache->{$name} = $data = $this->core->db->row( "SELECT * FROM ".DB_USER." WHERE user_mail = '$email' LIMIT 1" );
        	$ename = 'user' . $data['user_id'];
        	$cache->{$ename} = $data;
        } else $data = $cache->{$name};

		return $field ? $data[$field] : $data;

	}

    public function edit ( $data ) {

		$ups = array ();
        foreach ( $data as $k => $v ) $ups[ 'user_' . $k ] = $v;
		return $this->set ( $this->id, $ups );

    }

    // Destructor
    // Updating Data On Change
    public function __destruct () { }

} // class sitecache end

?>