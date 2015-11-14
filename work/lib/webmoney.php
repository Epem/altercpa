<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / webmoney.php
 *  Description:	WebMoney payment processing
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

// Checking Purse
if ( $core->post['LMI_PAYEE_PURSE'] != WMR ) die ();

// Checking Payment ID
$id = (int) $core->post['LMI_PAYMENT_NO'];
if ( $id ) {
	$user = $core->db->row( "SELECT * FROM ".DB_USER." WHERE user_id = '$id' LIMIT 1" );
	if ( ! $user['user_id'] ) die();
} else die();

// Checkign PreRequest
if ( ! $core->post['LMI_PREREQUEST'] ) {

	// Checking Hash
	$hash = WMR . $core->post['LMI_PAYMENT_AMOUNT'] . $core->post['LMI_PAYMENT_NO'] . $core->post['LMI_MODE'] . $core->post['LMI_SYS_INVS_NO'] . $core->post['LMI_SYS_TRANS_NO'] . $core->post['LMI_SYS_TRANS_DATE'] . WMK . $core->post['LMI_PAYER_PURSE'] . $core->post['LMI_PAYER_WM'];
	$hash = strtoupper(md5($hash));
	if ( $hash != $core->post['LMI_HASH'] ) die ();

	// Processing
	require_once ( PATH_LIB . 'finance.php' );
	$f = new Finance ( $core );
	$f->add( $id, 0, $core->post['LMI_PAYMENT_AMOUNT'], 1, $core->post['LMI_PAYER_PURSE'] );

} else echo 'YES'; // Answer for WM-PreRequest
die ();