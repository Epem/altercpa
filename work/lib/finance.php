<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / finance.php
 *  Description:	Money library
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

class Finance {
	// Core container
	private $user;
	private $db;
	public function __construct ( $core ) {		$this->user	= $core->user;
		$this->db	= $core->db;
	}
	public function __destruct() { }

	// Add new operation
	public function add( $user, $order, $summ, $type, $comment ) {
		$user	= (int) $user;
		$order	= (int) $order;
		$summ	= (int) $summ;
		$type	= (int) $type;

		$this->db->query( "INSERT INTO ".DB_CASH." SET user_id = '$user', order_id = '$order', cash_type = '$type', cash_value = '$summ', cash_descr = '$comment', cash_time = '".time()."'" );
		$id = $this->db->lastid();
		if ( $id ) {			$this->db->query( "UPDATE ".DB_USER." SET user_cash = user_cash ".( ($summ > 0) ? "+ $summ" : $summ )." WHERE user_id = '$user' LIMIT 1" );
			$this->user->reset( $user );
			return $id;
		} else return false;

	}

	// Change the operation type
	public function edit( $id, $type ) {

		$id		= (int) $id;
		$type	= (int) $type;

		$t = $this->db->row( "SELECT * FROM ".DB_CASH." WHERE cash_id = '$id' LIMIT 1" );
		if ( $t['cash_id'] ) {
        	$this->db->query( "UPDATE ".DB_CASH." SET cash_type = '$type' WHERE cash_id = '$id' LIMIT 1" );
        	return true;
		} else return false;

	}

	// Delete the operation
	public function del( $id ) {		$id = (int) $id;

		$t = $this->db->row( "SELECT * FROM ".DB_CASH." WHERE cash_id = '$id' LIMIT 1" );
		if ( $t['cash_id'] ) {			$this->db->query( "DELETE FROM ".DB_CASH." WHERE cash_id = '$id' LIMIT 1" );
        	$this->recount( $t['user_id'] );
        	return true;
		} else return false;

	}

	// Recount all user's transactions
	public function recount( $user ) {
		$user = (int) $user;
		$this->db->query( "UPDATE `".DB_USER."` SET `user_cash` = ( SELECT SUM(`cash_value`) FROM ".DB_CASH." WHERE `user_id` = '$user' ) WHERE `user_id` = '$user' LIMIT 1" );
		$this->user->reset( $user );

	}

}