<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / admin.php
 *  Description:	CPA Control Panel
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
// Specific funstions
//

// Business analytics
function analytics_line ( $c ) {

	$c['tt'] = $c['st0'] + $c['st5'] + $c['st6'];
	$c['pr0'] = sprintf( '%0.1f', $c['st0'] / $c['tt'] * 100 );
	$c['pr5'] = sprintf( '%0.1f', $c['st5'] / $c['tt'] * 100 );
	$c['pr6'] = sprintf( '%0.1f', $c['st6'] / $c['tt'] * 100 );
	$c['pr91'] = $c['st91'] ? sprintf( '%0.1f', $c['st91'] / ( $c['st91'] + $c['st101'] + $c['st111'] ) * 100 ) : 0;
	$c['pr101'] = $c['st101'] ? sprintf( '%0.1f', $c['st101'] / ( $c['st91'] + $c['st101'] + $c['st111'] ) * 100 ) : 0;
	$c['pr111'] = $c['st111'] ? sprintf( '%0.1f', $c['st111'] / ( $c['st91'] + $c['st101'] + $c['st111'] ) * 100 ) : 0;
	$c['pr92'] = $c['st92'] ? sprintf( '%0.1f', $c['st92'] / ( $c['st92'] + $c['st102'] + $c['st112'] ) * 100 ) : 0;
	$c['pr102'] = $c['st102'] ? sprintf( '%0.1f', $c['st102'] / ( $c['st92'] + $c['st102'] + $c['st112'] ) * 100 ) : 0;
	$c['pr112'] = $c['st112'] ? sprintf( '%0.1f', $c['st112'] / ( $c['st92'] + $c['st102'] + $c['st112'] ) * 100 ) : 0;
	$c['pr12'] = sprintf( '%0.1f', $c['st12'] / $c['tt'] * 100 );
	return $c;

}

//
// Main module
//

// Load the menu
function admin_menu ( $core, $menu ) {
	array_push( $menu, 'offer', 'comps', 'ext', 'outs', 'users', 'analytics' );
  	$menu['business'] = array( 'business', 'trans', 'dynamics' );
	return $menu;

}

// Process actions
function admin_action ( $core ) {
	$action = ( $core->get['a'] ) ? $core->get['a'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );

	switch ( $action ) {

	  //
	  // Files
	  //

	  case 'file-add':

		$ext = strtolower(substr( $core->files['file']['name'], strrpos( $core->files['file']['name'], '.' ) + 1 ));
       	$name = $core->text->link( $core->files['file']['name'] );
		$ge = array( 'jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip', 'rar', '7z', 'doc', 'docx', 'xls', 'xlsx', 'flv' );
		if ( in_array( $ext, $ge ) ) move_uploaded_file( $core->files['file']['tmp_name'], DIR_NEWS . $name );
	  	$core->go($core->url( 'm', 'files' ));

	  case 'file-del':

       	$name = $core->text->link( $core->get['name'] );
       	@unlink( DIR_NEWS . $name );
	  	$core->go($core->url( 'm', 'files' ));

	  //
	  // Users
	  //

	  // User Edit
	  case 'user-add':

		$name 	= $core->text->line ($core->post['name']);
		$email	= $core->text->email ($core->post['email']);
		$pass	= $core->text->pass ($core->post['pass']);
	  	$level	= $core->post['level'] ? 1 : 0;

		$mail_sql = ( $email ) ? ", user_mail = '$email' " : '';
		$pass_sql = ($core->post['pass']) ? ", user_pass = '$pass' " : '';

	    $sql = "INSERT INTO ".DB_USER." SET user_name = '$name', user_level = '$level' $pass_sql $mail_sql";
	    if ( $mail_sql && $pass_sql && $core->db->query ($sql) ) {
			$core->go($core->url( 'mm', 'users', 'add-ok' ));
	    } else $core->go($core->url( 'mm', 'users', 'add-e' ));

	  // User Edit
	  case 'user-edit':

		$old = $core->user->get( $id );
		$data = array(
			'user_name'			=> $core->text->line ($core->post['name']),
			'user_level'		=> ( $id == 1 ) ? 1 : ( $core->post['level'] ? 1 : 0 ),
			'user_ban'			=> ( $id == 1 ) ? 0 : ( $core->post['ban'] ? 1 : 0 ),
			'user_warn'			=> ( $id == 1 ) ? 0 : ( $core->post['warn'] ? 1 : 0 ),
			'user_work'			=> (int) $core->post['work'],
			'user_ext'			=> (int) $core->post['ext'],
			'user_comp'			=> (int) $core->post['comp'],
			'user_compad'		=> $core->post['compad']	? 1 : 0,
			'user_call'			=> $core->post['call']		? 1 : 0,
			'user_shave'		=> $core->post['shave']		? 1 : 0,
			'user_vip'			=> $core->post['vip']		? 1 : 0,
			'user_tariff'		=> (int) $core->post['tariff'],
		);

		if ( $email = $core->text->email ($core->post['email']) ) $data['user_mail'] = $email;
		if ( $core->post['pass'] ) $data['user_pass'] = $core->text->pass ($core->post['pass']);

	    if ( $core->user->set( $id, $data ) ) {

			// Money
			require_once ( PATH_LIB . 'finance.php' );
			$f = new Finance( $core );

			$money = (int) $core->post['money'];
			if ( $money ) {				$type = ( $money > 0 ) ? 1 : 5;
				$f->add( $id, 0, $money, $type, $core->lang['admin'] );
			} else $f->recount( $id );

			$core->wmsale->clear( 'mans', $comp );
			$core->wmsale->clear( 'allman' );

			$core->go($core->url( 'mm', 'users', 'edit-ok' ));

	    } else $core->go($core->url( 'mm', 'users', 'edit-e' ));

	  // User Delete
	  case 'user-del':

		if ( $id != 1 ) {

			$core->db->query( "DELETE FROM ".DB_CASH." WHERE user_id = '$id'" );
			$core->db->query( "DELETE FROM ".DB_STATS." WHERE user_id = '$id'" );
			$core->db->query( "DELETE FROM ".DB_FLOW." WHERE user_id = '$id'" );
			$core->db->query( "DELETE FROM ".DB_SUPP." WHERE supp_user = '$id'" );
			$core->db->query( "UPDATE ".DB_ORDER." SET wm_id = 0, flow_id = 0 WHERE wm_id = '$id'" );

			$comp = $core->db->field( "SELECT user_comp FROM ".DB_USER." WHERE user_id = '$id' LIMIT 1" );
			if ($core->db->query ( "DELETE FROM ".DB_USER." WHERE user_id = '$id'" )) {
				$core->wmsale->clear( 'mans', $comp );
				$core->wmsale->clear( 'allman' );
				$core->go($core->url( 'mm', 'users', 'del-ok' ));
			} else $core->go($core->url( 'mm', 'users', 'del-e' ));

	    } else $core->go($core->url( 'mm', 'users', 'del-a' ));

	  //
	  // Offers
	  //

	  // Offer Edit
	  case 'offer-add':

		$name 	= $core->text->line ( $core->post['name'] );
		$price	= (int) $core->post['price'];

	    $sql = "INSERT INTO ".DB_OFFER." SET offer_name = '$name', offer_price = '$price'";
	    if ( $core->db->query ($sql) ) {
			$id = $core->db->lastid();
			$core->wmsale->clear( 'offers' );
			$core->wmsale->clear( 'price' );
	        $core->go($core->url( 'im', 'offer', $id, 'add-ok' ));
	    } else $core->go($core->url( 'mm', 'offer', 'add-e' ));

	  // Offer Edit
	  case 'offer-edit':

		$comps = $core->wmsale->get( 'comps' );
		$mrt = array();
		foreach ( $core->post['mrt'] as $c => $d ) if ( ( $d = (int) $d ) > 0 ) $mrt[ (int) $c ] = $d;
		$mrt = $mrt ? serialize( $mrt ) : '';

		$data = array(
			'offer_name' 		=> $core->text->line ($core->post['name']),
			'offer_descr' 		=> $core->text->line ($core->post['descr']),
			'offer_text' 		=> $core->text->line ($core->post['text']),
			'offer_info' 		=> $core->text->code ($core->post['info']),
			'offer_price'		=> (int) $core->post['price'],
			'offer_country' 	=> $core->text->line ($core->post['country']),
			'offer_active'		=> $core->post['active'] ? 1 : 0,
			'offer_vars'		=> $core->post['vars'] ? 1 : 0,
			'offer_delivery'	=> $core->post['delivery'] ? 1 : 0,
			'offer_mr'			=> $core->post['mr'] ? 1 : 0,
			'offer_mrt'			=> $mrt,
			'offer_script' 		=> $core->text->line ($core->post['script']),
			'offer_payment'		=> (int) $core->post['payment'],
		);

	    if ( $core->db->edit( DB_OFFER, $data, "offer_id = '$id'" ) ) {			if ( $core->files['image'] ) {
				$ii = getimagesize( $core->files['image']['tmp_name'] );
				if ( $ii[2] == IMG_JPG ) move_uploaded_file( $core->files['image']['tmp_name'], sprintf( OFFER_FILE, $id ) );
			}
			$core->wmsale->clear( 'offer', $id );
			$core->wmsale->clear( 'ofp', $id );
			$core->wmsale->clear( 'offers' );
			$core->wmsale->clear( 'price' );
			$core->go($core->url( 'mm', 'offer', 'edit-ok' ));
	    } else $core->go($core->url( 'mm', 'offer', 'edit-e' ));

	  // Offer Special Prices
	  case 'offer-price':

		$price = array();
		foreach ( $core->post['wm']  as $u => $v ) if ( $v = (int) $v ) $price[ (int) $u ][0] = $v;
		foreach ( $core->post['pay'] as $u => $v ) if ( $v = (int) $v ) $price[ (int) $u ][1] = $v;
		foreach ( $core->post['ref'] as $u => $v ) if ( $v = (int) $v ) $price[ (int) $u ][2] = $v;
		foreach ( $core->post['wmu'] as $u => $v ) if ( $v = (int) $v ) $price[ (int) $u ][3] = $v;
		foreach ( $core->post['pyu'] as $u => $v ) if ( $v = (int) $v ) $price[ (int) $u ][4] = $v;
		$price = serialize( $price );

		$data = array(
			'offer_wm'			=> (int) $core->post['wmb'],
			'offer_wm_vip'		=> (int) $core->post['wmv'],
			'offer_wm_ext'		=> (int) $core->post['wme'],
			'offer_wmu'			=> (int) $core->post['wmub'],
			'offer_wmu_vip'		=> (int) $core->post['wmuv'],
			'offer_wmu_ext'		=> (int) $core->post['wmue'],
			'offer_pay'			=> (int) $core->post['payb'],
			'offer_pay_vip'		=> (int) $core->post['payv'],
			'offer_pay_ext'		=> (int) $core->post['paye'],
			'offer_pyu'			=> (int) $core->post['pyub'],
			'offer_pyu_vip'		=> (int) $core->post['pyuv'],
			'offer_pyu_ext'		=> (int) $core->post['pyue'],
			'offer_ref'			=> (int) $core->post['refb'],
			'offer_ref_vip'		=> (int) $core->post['refv'],
		 	'offer_prt' 		=> $price
		);

		if ( $core->db->edit( DB_OFFER, $data, "offer_id = '$id'" ) ) {			$core->wmsale->clear( 'offer', $id );
			$core->wmsale->clear( 'price' );
			$core->go($core->url( 'mm', 'offer', 'edit-ok' ));
	    } else $core->go($core->url( 'mm', 'offer', 'edit-e' ));

	  // Offer Params
	  case 'offer-param':

		$param = array();
		foreach ( $core->post['param'] as $u => $v1 ) {
			$u = (int) $u;
			$v1 = $core->text->link( $v1 );
			$v2 = stripslashes( $core->post['value'][$u] );
			if ( $v1 && $v2 ) $param[$v1] = $v2;
		}
		$param = addslashes(serialize( $param ));

		if ( $core->db->edit( DB_OFFER, array( 'offer_pars' => $param ), "offer_id = '$id'" ) ) {
			$core->wmsale->clear( 'offer', $id );
			$core->wmsale->clear( 'ofp', $id );
			$core->go($core->url( 'mm', 'offer', 'edit-ok' ));
	    } else $core->go($core->url( 'mm', 'offer', 'edit-e' ));

	  // Offer Delete
	  case 'offer-del':

		$sql = "DELETE FROM ".DB_OFFER." WHERE offer_id = '$id'";
		if ( $core->db->query( $sql ) ) {
			$core->db->query( "DELETE FROM ".DB_STORE." WHERE offer_id = '$id'" );
			$core->db->query( "DELETE FROM ".DB_ORDER." WHERE offer_id = '$id'" );
			$core->db->query( "DELETE FROM ".DB_FLOW." WHERE offer_id = '$id'" );
			$core->db->query( "DELETE FROM ".DB_STATS." WHERE offer_id = '$id'" );
			$core->db->query( "DELETE FROM ".DB_SITE." WHERE offer_id = '$id'" );
			$core->wmsale->clear( 'offer', $id );
			$core->wmsale->clear( 'offers' );
			$core->wmsale->clear( 'price' );
			$core->go($core->url( 'mm', 'offer', 'del-ok' ));
		} else $core->go($core->url( 'mm', 'offer', 'del-e' ));

	  // Offer Variant Add
	  case 'offer-var-add':

		$name 	= $core->text->line ( $core->post['name'] );
		$price	= (int) $core->post['price'];
		$vars = $core->db->field( "SELECT offer_vars FROM ".DB_OFFER." WHERE offer_id = '$id' LIMIT 1" );

	    if ( $vars && $core->db->add ( DB_VARS, array( 'offer_id' => $id, 'var_name' => $name, 'var_price' => $price ) ) ) {
			$id = $core->db->lastid();
			$core->wmsale->clear( 'vars', $id );
	        $core->go($core->url( 'im', 'offer-var', $id, 'add-ok' ));
	    } else $core->go($core->url( 'mm', 'offer-vars', 'add-e' ));

	  // Offer Variant Edit
	  case 'offer-var-edit':

		$name 	= $core->text->line( $core->post['name'] );
		$short 	= $core->text->line( $core->post['short'] );
		$price	= (int) $core->post['price'];
		$offer = $core->db->field( "SELECT offer_id FROM ".DB_VARS." WHERE var_id = '$id' LIMIT 1" );

	    $sql = "UPDATE ".DB_VARS." SET var_name = '$name', var_price = '$price', var_short = '$short' WHERE var_id = '$id' LIMIT 1";
	    if ( $core->db->query ($sql) ) {
			$core->wmsale->clear( 'vars', $offer );
			$core->go($core->url( 'im', 'offer-vars', $offer, 'edit-ok' ));
	    } else $core->go($core->url( 'im', 'offer-vars', $offer, 'edit-e' ));

	  // Offer Variant Delete
	  case 'offer-var-del':

		$offer = $core->db->field( "SELECT offer_id FROM ".DB_VARS." WHERE var_id = '$id' LIMIT 1" );
		if ($core->db->query ( "DELETE FROM ".DB_VARS." WHERE var_id = '$id'" )) {
			$core->wmsale->clear( 'vars', $offer );
			$core->go($core->url( 'im', 'offer-vars', $offer, 'del-ok' ));
		} else $core->go($core->url( 'im', 'offer-vars', $offer, 'del-e' ));

	  // Offer Site Add
	  case 'offer-site-add':

		$url = $core->text->line ( $core->post['url'] );
		$key = md5(microtime());

	    if ( $core->db->add ( DB_SITE, array( 'offer_id' => $id, 'site_url' => $url, 'site_key' => $key ) ) ) {
			$core->wmsale->clear( 'sites', $id );
			$core->wmsale->clear( 'lands', $id );
			$core->wmsale->clear( 'space', $id );
			$sid = $core->db->lastid();
			file_get_contents( SPACEURL . 'renew.php?id='.$id );
	        $core->go($core->url( 'im', 'offer-site', $sid, 'add-ok' ));
	    } else $core->go($core->url( 'mm', 'offer-sites', 'add-e' ));

	  // Offer Site Edit
	  case 'offer-site-edit':

		$url		= $core->text->line ( $core->post['url'] );
		$key		= $core->post['key'] ? $core->text->line ($core->post['key']) : md5(microtime());
		$comp		= (int) $core->post['comp'];
		$comph		= $core->post['comph'] ? 1 : 0;
		$type		= $core->post['type'] ? 1 : 0;
		$default	= $core->post['default'] ? 1 : 0;
		$mobile		= (int) $core->post['mobile'];
		$offer		= $core->db->field( "SELECT offer_id FROM ".DB_SITE." WHERE site_id = '$id' LIMIT 1" );

    	if ( $default ) $core->db->query( "UPDATE ".DB_SITE." SET site_default = 0 WHERE offer_id = '$offer' AND site_type = '$type'" );

	    $sql = "UPDATE ".DB_SITE." SET site_url = '$url', site_key = '$key', site_type = '$type', site_comp = '$comph', site_default = '$default', site_mobile = '$mobile', comp_id = '$comp' WHERE site_id = '$id' LIMIT 1";
	    if ( $core->db->query ($sql) ) {
			$core->wmsale->clear( 'site', $id );
			$core->wmsale->clear( 'sites', $offer );
			$core->wmsale->clear( 'lands', $offer );
			$core->wmsale->clear( 'space', $offer );
			file_get_contents( SPACEURL . 'renew.php?id='.$offer );
			$core->go($core->url( 'im', 'offer-sites', $offer, 'edit-ok' ));
	    } else $core->go($core->url( 'im', 'offer-sites', $offer, 'edit-e' ));

	  // Offer Site Delete
	  case 'offer-site-del':

		$offer = $core->db->field( "SELECT offer_id FROM ".DB_SITE." WHERE site_id = '$id' LIMIT 1" );
		if ($core->db->query ( "DELETE FROM ".DB_SITE." WHERE site_id = '$id'" )) {
			$core->wmsale->clear( 'site', $id );
			$core->wmsale->clear( 'sites', $offer );
			$core->wmsale->clear( 'lands', $offer );
			$core->wmsale->clear( 'space', $offer );
			file_get_contents( SPACEURL . 'renew.php?id='.$offer );
			$core->go($core->url( 'im', 'offer-sites', $offer, 'del-ok' ));
		} else $core->go($core->url( 'im', 'offer-sites', $offer, 'del-e' ));

	  case 'offer-site-renew':

		file_get_contents( SPACEURL . 'renew.php?id='.$id );
		$core->go($core->url( 'im', 'offer-sites', $id, 'ok' ));

	  case 'offer-site-list':

		header( 'Content-disposition: attachment; filename=offer'.$id.'.php' );
	  	header( 'Content-type: text/plain; charset=utf-8' );
		$lands = $core->wmsale->get( 'lands', $id );
		$space = $core->wmsale->get( 'space', $id );
		$default = 0;
		$elands = $espace = array();
		foreach ( $lands as $l ) {			if ( ! $default ) $default = $l['site_id'];
			if ( $l['site_default'] ) $default = $l['site_id'];
			$elands[$l['site_id']] = 'http://' . $l['site_url'] . '/?';
		}
		foreach ( $space as $l ) $espace[$l['site_url']] = (int) $l['site_id'];

echo '<?
require_once "cms.php";
function ourl () {
static $theurl;
global $flow;
if ( $theurl ) return $theurl;
$defland = '.$default.';
$lands = '; var_export( $elands ); echo ';
$space = '; var_export( $espace ); echo ';
$theurl = geturl ( $lands, $space, $defland );
return $theurl;
}';
		$core->_die();

	  //
	  // Companies
	  //

	  // Adding a company
	  case 'comps-add':

	    if ( $core->db->add( DB_COMP, array( 'comp_name' => $core->text->line( $core->post['name'] ) ) ) ) {
			$core->wmsale->clear( 'comps' );
			$core->go($core->url( 'im', 'comps', $core->db->lastid(), 'add-ok' ));
	    } else $core->go($core->url( 'mm', 'comps', 'add-e' ));

	  // Edit company info
	  case 'comps-edit':

		$edit = array(
			'user_id'			=> (int) $core->post['user'],
			'comp_name'			=> $core->text->line ( $core->post['name'] ),
			'comp_fio'			=> $core->text->line ( $core->post['fio'] ),
			'comp_phone'		=> $core->text->line ( $core->post['phone'] ),
			'comp_index'		=> preg_replace( '#([^0-9]+)#', '', $core->post['index'] ),
			'comp_addr'			=> $core->text->line ( $core->post['addr'] ),
			'comp_bank'			=> $core->text->line ( $core->post['bank'] ),
			'comp_acc'			=> preg_replace( '#([^0-9]+)#', '', $core->post['acc'] ),
			'comp_ks'			=> preg_replace( '#([^0-9]+)#', '', $core->post['ks'] ),
			'comp_bik'			=> preg_replace( '#([^0-9]+)#', '', $core->post['bik'] ),
			'comp_inn'			=> preg_replace( '#([^0-9]+)#', '', $core->post['inn'] ),
			'comp_spsr'			=> $core->text->line ( $core->post['spsr'] ),
			'comp_spsr_login'	=> $core->text->line ( $core->post['spsr_login'] ),
			'comp_spsr_pass'	=> $core->text->line ( $core->post['spsr_pass'] ),
			'comp_spsr_from'	=> $core->text->line ( $core->post['spsr_from'] ),
			'sms_accept'		=> $core->post['sms_accept'] ? 1 : 0,
			'sms_post'			=> $core->post['sms_post'] ? 1 : 0,
			'sms_spsr'			=> $core->post['sms_spsr'] ? 1 : 0,
			'sms_rupo'			=> $core->post['sms_rupo'] ? 1 : 0,
			'autoaccept'		=> $core->post['autoaccept'] ? 1 : 0,
			'callscheme'		=> $core->text->line ( $core->post['callscheme'] ),
			'pay_info'			=> $core->text->code ( $core->post['pay_info'] ),
			'pay_wmr'			=> $core->text->line ( $core->post['pay_wmr'] ),
			'pay_wmk'			=> $core->text->line ( $core->post['pay_wmk'] ),
			'pay_ymr'			=> $core->text->line ( $core->post['pay_ymr'] ),
			'pay_ymk'			=> $core->text->line ( $core->post['pay_ymk'] ),
		);

		if ( $core->db->edit( DB_COMP, $edit, "comp_id = '$id'" ) ) {
			$core->wmsale->clear( 'comp', $id );
			$core->wmsale->clear( 'comps' );
			$core->go($core->url( 'mm', 'comps', 'edit-ok' ));
	    } else $core->go($core->url( 'mm', 'comps', 'edit-e' ));

	  // Company Delete
	  case 'comps-del':

		if ($core->db->query ( "DELETE FROM ".DB_COMP." WHERE comp_id = '$id' LIMIT 1" )) {
			$core->db->query( "DELETE FROM ".DB_USER." WHERE user_comp = '$id'" );
			$core->db->query( "DELETE FROM ".DB_ORDER." WHERE comp_id = '$id'" );
			$core->db->query( "DELETE FROM ".DB_STORE." WHERE comp_id = '$id'" );
			$core->wmsale->clear( 'comp', $id );
			$core->wmsale->clear( 'comps' );
			$core->go($core->url( 'mm', 'comps', 'del-ok' ));
		} else $core->go($core->url( 'mm', 'comps', 'del-e' ));

	  // Edit company info
	  case 'comps-int':

		$field = array();
		$flds = explode( "\n", $core->post['add_field'] );
		if ( $flds ) foreach ( $flds as $k ) {
			$kk = explode( ' ', trim( $k ), 2 );
			$field[$kk[0]] = stripslashes(trim( $kk[1] ));
		}
		$field = addslashes(serialize( $field ));

		$field2 = array();
		$flds2 = explode( "\n", $core->post['chk_field'] );
		if ( $flds2 ) foreach ( $flds2 as $k ) {
			$kk = explode( ' ', trim( $k ), 2 );
			$field2[$kk[0]] = stripslashes(trim( $kk[1] ));
		}
		$field2 = addslashes(serialize( $field2 ));

		$edit = array(
			'int_add'			=> $core->post['add'] ? 1 : 0,
			'int_add_url'		=> str_replace( '&amp;', '&', str_replace( '&quot;', '"', $core->text->line( $core->post['add_url'] ) ) ),
			'int_add_pre'		=> $core->text->code( $core->post['add_pre'] ),
			'int_add_field'		=> $field,
			'int_add_code'		=> $core->text->code( $core->post['add_code'] ),
			'int_chk'			=> $core->post['chk'] ? 1 : 0,
			'int_chk_url'		=> str_replace( '&amp;', '&', str_replace( '&quot;', '"', $core->text->line( $core->post['chk_url'] ) ) ),
			'int_chk_pre'		=> $core->text->code( $core->post['chk_pre'] ),
			'int_chk_field'		=> $field2,
			'int_chk_format'	=> (int) $core->post['chk_format'],
			'int_chk_count'		=> (int) $core->post['chk_count'],
			'int_chk_code'		=> $core->text->code( $core->post['chk_code'] ),
		);

		if ( $core->db->edit( DB_COMP, $edit, "comp_id = '$id'" ) ) {
			$core->wmsale->clear( 'comp', $id );
			$core->go($core->url( 'mm', 'comps', 'edit-ok' ));
	    } else $core->go($core->url( 'mm', 'comps', 'edit-e' ));

	  //
	  // Externals
	  //

	  // Adding an external
	  case 'ext-add':

	    if ( $core->db->add( DB_EXT, array( 'ext_name' => $core->text->line( $core->post['name'] ) ) ) ) {
			$core->wmsale->clear( 'exts' );
			$core->go($core->url( 'im', 'ext', $core->db->lastid(), 'add-ok' ));
	    } else $core->go($core->url( 'mm', 'ext', 'add-e' ));

	  // Edit external info
	  case 'ext-edit':

		$edit = array(
			'user_id'			=> (int) $core->post['user'],
			'ext_name'			=> $core->text->line ( $core->post['name'] ),
			'ext_key'			=> $core->text->line ( $core->post['key'] ),
			'url_new'			=> str_replace( '&amp;', '&', $core->text->line( $core->post['url_new'] ) ),
			'url_nc'			=> str_replace( '&amp;', '&', $core->text->line( $core->post['url_nc'] ) ),
			'url_rc'			=> str_replace( '&amp;', '&', $core->text->line( $core->post['url_rc'] ) ),
			'url_acc'			=> str_replace( '&amp;', '&', $core->text->line( $core->post['url_acc'] ) ),
			'url_dec'			=> str_replace( '&amp;', '&', $core->text->line( $core->post['url_dec'] ) ),
			'url_pay'			=> str_replace( '&amp;', '&', $core->text->line( $core->post['url_pay'] ) ),
			'url_ret'			=> str_replace( '&amp;', '&', $core->text->line( $core->post['url_ret'] ) ),
			'url_del'			=> str_replace( '&amp;', '&', $core->text->line( $core->post['url_del'] ) ),
			'code_offer'		=> $core->text->code( $core->post['code_offer'] ),
			'code_accept'		=> $core->text->code( $core->post['code_accept'] ),
		);

		if ( $core->db->edit( DB_EXT, $edit, "ext_id = '$id'" ) ) {
			$core->wmsale->clear( 'ext', $id );
			$core->wmsale->clear( 'exts' );
			$core->go($core->url( 'mm', 'ext', 'edit-ok' ));
	    } else $core->go($core->url( 'mm', 'ext', 'edit-e' ));

	  // Delete external
	  case 'ext-del':

		if ($core->db->query ( "DELETE FROM ".DB_EXT." WHERE ext_id = '$id' LIMIT 1" )) {
			$core->db->query( "DELETE FROM ".DB_USER." WHERE user_ext = '$id'" );
			$core->db->query( "UPDATE ".DB_ORDER." SET ext_id = 0, ext_uid = 0, ext_src = 0 WHERE ext_id = '$id'" );
			$core->wmsale->clear( 'ext', $id );
			$core->wmsale->clear( 'exts' );
			$core->go($core->url( 'mm', 'ext', 'del-ok' ));
		} else $core->go($core->url( 'mm', 'ext', 'del-e' ));

	  //
	  // Outputs
	  //

	  case 'out-accept':

		$c = $core->db->row( "SELECT * FROM ".DB_CASH." WHERE cash_id = '$id' LIMIT 1" );
		if ( $c['cash_type'] == 4 ) {
			require_once ( PATH_LIB . 'finance.php' );
	    	$f = new Finance( $core );
	    	if ( $f->edit( $id, 5 ) ) {
	        	$core->go($core->url( 'mm', 'outs', 'acc-ok' ));
	    	} else $core->go($core->url( 'mm', 'outs', 'acc-e' ));
		} else $core->go($core->url( 'mm', 'outs', 'acc-e' ));

	  case 'out-decline':

		$c = $core->db->row( "SELECT * FROM ".DB_CASH." WHERE cash_id = '$id' LIMIT 1" );
		if ( $c['cash_type'] == 4 ) {
			require_once ( PATH_LIB . 'finance.php' );
	    	$f = new Finance( $core );
	    	if ( $f->del( $id ) ) {
	        	$core->go($core->url( 'mm', 'outs', 'dec-ok' ));
	    	} else $core->go($core->url( 'mm', 'outs', 'dec-e' ));
		} else $core->go($core->url( 'mm', 'outs', 'dec-e' ));

	  case 'out-bulk':

		$outs = array(); foreach ( $core->post['ids'] as $i ) if ( $i = (int) $i ) $outs[] = $i;
		$otp = $core->db->col( "SELECT cash_id FROM ".DB_CASH." WHERE cash_id IN ( ".implode( ',', $outs )." ) AND cash_type = 4" );

		require_once ( PATH_LIB . 'finance.php' );
    	$f = new Finance( $core );

		if ( $core->post['decline'] ) {        	foreach ( $otp as $id ) $f->del( $id );
		} else foreach ( $otp as $id ) $f->edit( $id, 5 );

		$core->go($core->url( 'mm', 'outs', 'ok' ));

	  //
	  // News
	  //

	  case 'news-add':

		$title	= $core->text->line( $core->post['title'] );
		$text	= $core->text->code( $core->post['text'] );
		$group	= (int) $core->post['group'];
		$send	= $core->post['send'] ? 1 : 0;
		$vip	= $core->post['vip'] ? 1 : 0;
		$mvip	= $vip ? ' AND user_vip = 1 ' : '';

	    if ( $core->db->add ( DB_NEWS, array( 'news_title' => $title, 'news_text' => $text, 'news_group' => $group, 'news_time' => time(), 'news_vip' => $vip ) ) ) {
			$id = $core->db->lastid();
			if ( $send ) {             	switch ( $group ) {
             		case 1:		$mails = $core->db->col( "SELECT user_mail FROM ".DB_USER." WHERE user_news = 1 AND user_work = 0 $mvip" ); break;
             		case 2:     $mails = $core->db->col( "SELECT user_mail FROM ".DB_USER." WHERE user_news = 1 AND user_work = 1 $mvip" ); break;
             		default:    $mails = $core->db->col( "SELECT user_mail FROM ".DB_USER." WHERE user_news = 1 $mvip" );
             	}
             	$core->email->send( $mails, sprintf( $core->lang['mail_news_h'], stripslashes( $title ) ), sprintf( $core->lang['mail_news_t'], stripslashes( $text ), $id ) );
			} $core->go($core->url( 'mm', 'news', 'ok' ));
	    } else $core->go($core->url( 'mm', 'news', 'e' ));

	  // Offer Site Edit
	  case 'news-edit':

		$title	= $core->text->line( $core->post['title'] );
		$text	= $core->text->code( $core->post['text'] );
		$group	= (int) $core->post['group'];
		$send	= $core->post['send'] ? 1 : 0;
		$vip	= $core->post['vip'] ? 1 : 0;
		$mvip	= $vip ? ' AND user_vip = 1 ' : '';

	    if ( $core->db->edit ( DB_NEWS, array( 'news_title' => $title, 'news_text' => $text, 'news_group' => $group, 'news_vip' => $vip ), "news_id = '$id'" ) ) {
			if ( $send ) {
             	switch ( $group ) {
             		case 1:		$mails = $core->db->col( "SELECT user_mail FROM ".DB_USER." WHERE user_news = 1 AND user_work = 0 $mvip" ); break;
             		case 2:     $mails = $core->db->col( "SELECT user_mail FROM ".DB_USER." WHERE user_news = 1 AND user_work = 1 $mvip" ); break;
             		default:    $mails = $core->db->col( "SELECT user_mail FROM ".DB_USER." WHERE user_news = 1 $mvip" );
             	}
             	$core->email->send( $mails, sprintf( $core->lang['mail_news_h'], stripslashes( $title ) ), sprintf( $core->lang['mail_news_t'], stripslashes( $text ), $id ) );
			} $core->go($core->url( 'mm', 'news', 'ok' ));
	    } else $core->go($core->url( 'mm', 'news', 'e' ));

	  // Offer Site Delete
	  case 'news-del':

	    if ( $core->db->del ( DB_NEWS, "news_id = '$id'" ) ) {
	        $core->go($core->url( 'mm', 'news', 'ok' ));
	    } else $core->go($core->url( 'mm', 'news', 'e' ));

	  //
	  // Support
	  //

	  case 'supp-add':

	  	require_once PATH_LIB . 'support.php';
		support_add( $core, $id, 1, $core->post['text'] );
		if ( $core->get['z'] == 'ajax' ) {
			echo 'ok'; $core->_die();
		} else $core->go($core->url ( 'i', 'support', $id ));

	  case 'supp-show':

		require_once PATH_LIB . 'support.php';
		$messages = support_show( $core, $id, 1, $core->get['from'] );
		$email = $core->user->get( $id, 'user_mail' );

		if ( $mc = count( $messages ) ) {
			$core->tpl->load( 'body', 'message' );
			$mn = $mx = $mm = 0;
			foreach ( $messages as &$m ) {				$core->tpl->block( 'body', 'msg', $m );
				if ( $m['uid'] == $id ) $core->tpl->block( 'body', 'msg.admin', array( 'u' => $email ) );
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
			if ( $core->get['from'] >= 0 ) {
				$core->tpl->block( 'body', 'more' );
			} else $core->tpl->block( 'body', 'havemsg' );
			$core->tpl->output( 'body' );
		} $core->_die();

	  //
	  // Accounting
	  //

	  case 'saw':

		$sum = (int) $core->post['sum'];
		$users = array(); foreach ( $core->post['user'] as $u ) if ( $u ) $users[] = (int) $u;
		$tosaw = count( $users );

		$sum = floor( $sum / $tosaw );
		require_once PATH_LIB . 'finance.php';
		$f = new Finance( $core );
		foreach ( $users as $u ) {
			$f->add( $u, 0, $sum, 13, $core->lang['exit_comment'] );
			$f->add( $u, 0, -$sum, 5, $core->lang['exit_comment'] );
		}

		$core->go($core->url( 'mm', 'business', 'saw' ));

	  case 'trans-del':

		require_once PATH_LIB . 'finance.php';
		$f = new Finance( $core );
		$f->del( $id );
		msgo( $core, 'del' );

	}

	return false;

}

function admin_module ( $core ) {
	$module	= ( $core->get['m'] ) ? $core->get['m'] : null;
	$id		= ( $core->post['id'] ) ? (int) $core->post['id'] : ( ($core->get['id']) ? (int) $core->get['id'] : 0 );
	$page	= ( $core->get['page'] > 0 ) ? (int) $core->get['page'] : 1;
	$message = ( $core->get['message'] ) ? $core->get['message'] : null;

	switch ( $module ) {

	  case 'files':

		$d = opendir( DIR_NEWS );
		$files = array();
		while ( $f = readdir ( $d ) ) {			if ( is_file( DIR_NEWS . $f ) ) $files[] = $f;
		} closedir ( $d );
		sort( $files );

		$core->tpl->load( 'body', 'files' );
		$core->tpl->vars( 'body', array( 'upload' => $core->url( 'a', 'file-add', 0 ) ) );

		foreach ( $files as $f ) {        	$core->tpl->block( 'body', 'file', array(
				'url'	=> sprintf( PATH_NEWS, $f ),
				'size'	=> mkb_out( filesize( DIR_NEWS . $f ) ),
				'time'	=> date( 'd.m.Y H:i:s', filemtime( DIR_NEWS . $f ) ),
				'name'	=> $f,
				'del'	=> $core->url( 'a', 'file-del', 0 ) . '?name='. $f
        	));
		}

		$core->tpl->output( 'body' );
		$core->_die();

	  // Users List
	  case 'users':

		switch ( $message ) {
	    	case 'add-ok':	$core->info( 'info', 'done_user_add' ); break;
	    	case 'edit-ok':	$core->info( 'info', 'done_user_edit' ); break;
	    	case 'del-ok':	$core->info( 'info', 'done_user_del' ); break;
	    	case 'add-e':	$core->info( 'error', 'error_user_add' ); break;
	    	case 'edit-e':	$core->info( 'error', 'error_user_edit' ); break;
	    	case 'del-e':	$core->info( 'error', 'error_user_del' ); break;
	    	case 'del-a':	$core->info( 'error', 'error_user_root' ); break;
		}

	  	if ( $id ) {

	    	$user = $core->db->row ( "SELECT * FROM ".DB_USER." WHERE user_id = '$id' LIMIT 1" );

		    $core->mainline->add ( $core->lang['admin_user_h'], $core->url('m', 'users') );
		    $core->mainline->add ( $user['user_name'] );
		    $core->header ();

			$work = array(); foreach ( $core->lang['user_works'] as $i => $v ) $work[] = array( 'name' => $v, 'value' => $i, 'select' => $user['user_work'] == $i );

			$comps = $core->wmsale->get( 'comps' );
			$comp = array(array( 'name' => '---', 'value' => 0 ));
			foreach ( $comps as $i => $c ) $comp[] = array( 'name' => $c, 'value' => $i, 'select' => $i == $user['user_comp'] );

			$exts = $core->wmsale->get( 'exts' );
			$ext = array(array( 'name' => '---', 'value' => 0 ));
			foreach ( $exts as $i => $c ) $ext[] = array( 'name' => $c, 'value' => $i, 'select' => $i == $user['user_ext'] );

		    $title	= $core->lang['user_edit'];
		    $action	= $core->url ( 'a', 'user-edit', $id );
		    $method	= 'post';
		    $field 	= array(
	            array( 'type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['user_name'], 'descr' => $core->lang['user_name_d'], 'value' => $user['user_name']),
	            array( 'type' => 'text', 'length' => 100, 'name' => 'email', 'head' => $core->lang['user_email'], 'descr' => $core->lang['user_email_d'], 'value' => $user['user_mail']),
	            array( 'type' => 'text', 'length' => 32, 'name' => 'pass', 'head' => $core->lang['user_pass'], 'descr' => $core->lang['user_pass_d'] ),
	            array( 'type' => 'checkbox', 'name' => 'level', 'head' => $core->lang['user_level'], 'descr' => $core->lang['user_level_d'], 'checked' => $user['user_level'] ),
	            array( 'type' => 'checkbox', 'name' => 'ban', 'head' => $core->lang['user_ban'], 'descr' => $core->lang['user_ban_d'], 'checked' => $user['user_ban'] ),
	            array( 'type' => 'checkbox', 'name' => 'warn', 'head' => $core->lang['user_warn'], 'descr' => $core->lang['user_warn_d'], 'checked' => $user['user_warn'] ),
	            array( 'type' => 'checkbox', 'name' => 'vip', 'head' => $core->lang['comp_vip'], 'descr' => $core->lang['comp_vip_d'], 'checked' => $user['user_vip'] ),
	            array( 'type' => 'select', 'name' => 'work', 'head' => $core->lang['user_work'], 'descr' => $core->lang['user_work_d'], 'value' => $work ),
	            array( 'type' => 'select', 'name' => 'comp', 'head' => $core->lang['company'], 'value' => $comp ),
	            array( 'type' => 'select', 'name' => 'ext', 'head' => $core->lang['agency'], 'value' => $ext ),
	            array( 'type' => 'checkbox', 'name' => 'compad', 'head' => $core->lang['user_compad'], 'descr' => $core->lang['user_compad_d'], 'checked' => $user['user_compad'] ),
	            array( 'type' => 'checkbox', 'name' => 'call', 'head' => $core->lang['user_call'], 'descr' => $core->lang['user_call_d'], 'checked' => $user['user_call'] ),
	            array( 'type' => 'checkbox', 'name' => 'shave', 'head' => $core->lang['user_shave'], 'descr' => $core->lang['user_shave_d'], 'checked' => $user['user_shave'] ),
	            array( 'type' => 'text', 'length' => 5, 'name' => 'tariff', 'head' => $core->lang['tariff'],  'value' => $user['user_tariff']),
	            array( 'type' => 'text', 'length' => 7, 'name' => 'money', 'head' => $core->lang['user_money'],  'descr' => $core->lang['user_money_d'], ),
		    );
		    $button = array(array('type' => 'submit', 'value' => $core->lang['save']));
		    $core->form ('useredit', $action, $method, $title, $field, $button);

	        $core->footer ();

	    } else {

			$today = date( 'Ymd' );
			$m1m = date( 'Ymd', strtotime( '-2 weeks' ) );
			$m2m = date( 'Ymd', strtotime( '-1 months' ) );
			$where = array();

			if ( isset( $core->get['s'] ) && $core->get['s'] ) {
				require_once PATH_CORE . 'search.php';
				$search = new SearchWords( $core->get['s'] );
				if ( $s = $search->get() ) {
					$where[] = $search->field(array( 'user_name', 'user_mail' ));
				} else $s = false;
			} else $s = false;

			if ( isset( $core->get['c'] ) && $core->get['c'] ) {
				$c = (int) $core->get['c'];
				$where[] = "user_comp = '$c'";
			} else $c = false;

			if ( isset( $core->get['l'] ) && $core->get['l'] != '' ) {				$l = (int) $core->get['l'];
				$where[] = "user_work = '$l'";
			} else $l = null;

			$where = count( $where ) ? implode( ' AND ', $where ) : '1';
		    $sh = 30; $st = $sh * ( $page - 1 );
	    	$users	= $core->db->field ( "SELECT COUNT(*) FROM ".DB_USER." WHERE $where");
	    	$user	= $users ? $core->db->data ( "SELECT * FROM ".DB_USER." WHERE $where ORDER BY user_work DESC, user_id ASC LIMIT $st, $sh" ) : array();
			$comp	= $core->wmsale->get( 'comps' );
			$ext 	= $core->wmsale->get( 'exts' );

		    $core->mainline->add ( $core->lang['admin_user_h'], $core->url('m', 'users') );
		    $core->header ();

		    $core->tpl->load( 'body', 'users' );

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
	            'pages'			=> pages ( $core->url('m', 'users?') . ( $c ? 'c=' . $c . '&' : '' ) . ( $l !== null ? 'l=' . $l . '&' : '' ) . ( $s ? 's=' . $s : '' ), $users, $sh, $page ),
	            'shown'			=> sprintf( $core->lang['shown'], $st+1, min( $st+$sh, $users ), $users ),
				'search'		=> $core->lang['search'],
				'find'			=> $core->lang['find'],
		    ));

			foreach ( $comp as $ci => $cn ) {
				$core->tpl->block( 'body', 'comp', array(
					'name'		=> $cn,
					'value'		=> $ci,
					'select'	=> ( $c == $ci ) ? 'selected="selected"' : '',
				));
			}

			foreach ( $core->lang['user_works'] as $li => $ln ) {
				$core->tpl->block( 'body', 'level', array(
					'name'		=> $ln,
					'value'		=> $li,
					'select'	=> ( $l !== null && $l == $li ) ? 'selected="selected"' : '',
				));
			}

		    foreach ( $user as &$i ) {    			$key = $i['user_id'] . md5(crypto::encode( $i['user_mail'] . $i['user_pass'], $core->crypto ));
		        $core->tpl->block ('body', 'user', array (
		            'id'        => $i['user_id'],
		            'name'		=> $search ? $search->highlight( $i['user_name'] ) : $i['user_name'],
		            'email'		=> $search ? $search->highlight( $i['user_mail'] ) : $i['user_mail'],
		            'mailto'	=> $i['user_mail'],
		            'vip'		=> $i['user_vip'] ? $core->lang['iamvip'] : '',
		            'level'		=> $i['user_level'] ? '<b class="boss" title="'.$core->lang['admin'].'">'.$core->lang['user_works'][$i['user_work']].'</b>' : $core->lang['user_works'][$i['user_work']],
		            'icon'		=> $i['user_ban'] ? 'block' : ( $i['user_warn'] ? 'isua' : 'isok' ),
		            'u_level'	=> $core->url('m', 'users?l=') . $i['user_work'] . ( $c ? '&c=' . $c : '' ) . ( $s ? '&s=' . $s : '' ),
		            'enter'		=> sprintf( ( $i['user_ref'] == 119 || $i['user_sub'] == 119 || $i['user_id'] == 119 ) ? $core->lang['mail_recover_r'] : $core->lang['mail_recover_u'], $key ),
		            'cash'      => rur( $i['user_cash'] ),
		            'flw'		=> (int) $i['user_flw'],
		            'flwa'		=> (int) $i['user_flwa'],
		            'cr'		=> ( $i['user_cr'] < 10 ) ? sprintf( "%0.2f", $i['user_cr'] ) : sprintf( "%0.1f", $i['user_cr'] ),
		            'crc'		=> ( $i['user_cr'] < 10 ) ? ( ($i['user_cr'] < 5) ? 'green' : 'yellow' ) : ( ($i['user_cr'] > 20) ? 'red fat' : 'red' ),
		            'epc'		=> rur( $i['user_epc'] ),
		            'comp'		=> $i['user_comp'] ? ( $i['user_compad'] ? '<b class="boss" title="'.$core->lang['admin'].'">'.$comp[$i['user_comp']].'</b>' : $comp[$i['user_comp']] ) : ( $i['user_ref'] ? $core->user->get( $i['user_ref'], 'user_name' ) : $ext[$i['user_ext']] ),
		            'sclass'	=> $i['supp_last'] ? ( $i['supp_admin'] ? 'suppst-new' : ( $i['supp_type'] ? ( $i['supp_new'] ? 'suppst-ur' : 'suppst-ok' ) : 'suppst-ua' ) ) : 'help',
		            'u_comp'	=> $core->url('m', 'users?c=') . $i['user_comp'] . ( $l !== null ? '&l=' . $l : '' ) . ( $s ? '&s=' . $s : '' ),
		            'support'	=> $core->url ('i', 'support', $i['user_id'] ),
		            'orders'	=> $core->url ('m', 'order' ) . '?wm=' . $i['user_id'],
		            'url'       => $core->url ('i', 'users', $i['user_id'] ),
		            'edit'      => $core->url ('i', 'users', $i['user_id'] ),
		            'del'       => $core->url ('a', 'user-del', $i['user_id'] ),
		            'ip'		=> $i['user_ip'] ? int2ip( $i['user_ip'] ) : '',
		            'date'		=> $i['user_date'] ? date2form( $i['user_date'] ) : '',
		            'dclass'	=> ( $i['user_date'] == $today ) ? 'fat green' : ( ( $i['user_date'] < $m1m ) ? ( ( $i['user_date'] < $m2m ) ? 'red' : 'yellow' ) : '' ),
		        ));
		    } unset ($d);

			$core->tpl->output ('body');

		    $title	= $core->lang['user_add'];
		    $action	= $core->url ( 'a', 'user-add', 0 );
		    $method	= 'post';
		    $field 	= array(
	            array('type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['user_name'], 'descr' => $core->lang['user_name_d'] ),
	            array('type' => 'text', 'length' => 100, 'name' => 'email', 'head' => $core->lang['user_email'], 'descr' => $core->lang['user_email_d'] ),
	            array('type' => 'pass', 'length' => 32, 'name' => 'pass', 'head' => $core->lang['user_pass'], 'descr' => $core->lang['user_pass_d'] ),
		    );
		    $button = array(array('type' => 'submit', 'value' => $core->lang['save']));
		    $core->form ( 'useradd', $action, $method, $title, $field, $button );

	        $core->footer ('admin');

	    }

	  	$core->_die ();

	  // Offers
	  case 'offer':

		switch ( $message ) {
	    	case 'add-ok':	$core->info( 'info', 'done_offer_add' ); break;
	    	case 'edit-ok':	$core->info( 'info', 'done_offer_edit' ); break;
	    	case 'del-ok':	$core->info( 'info', 'done_offer_del' ); break;
	    	case 'add-e':	$core->info( 'error', 'error_offer_add' ); break;
	    	case 'edit-e':	$core->info( 'error', 'error_offer_edit' ); break;
	    	case 'del-e':	$core->info( 'error', 'error_offer_del' ); break;
		}

	  	if ( $id ) {

	    	$offer = $core->db->row ( "SELECT * FROM ".DB_OFFER." WHERE offer_id = '$id' LIMIT 1" );
	    	$mrt = $offer['offer_mrt'] ? unserialize( $offer['offer_mrt'] ) : array();
			$comps = $core->wmsale->get( 'comps' );

			$payment = array();
			foreach ( $core->lang['offer_payments'] as $v => $n ) $payment[] = array( 'name' => $n, 'value' => $v, 'select' => $v == $offer['offer_payment'] );

		    $core->mainline->add ( $core->lang['offers_h'], $core->url('m', 'offer') );
		    $core->mainline->add ( $offer['offer_name'] );
		    $core->header ();

		    $title	= $core->lang['offer_edit_h'];
		    $action	= $core->url ( 'a', 'offer-edit', $id );
		    $method	= 'post';
		    $field 	= array(
		    	array('type' => 'line', 'value' => $core->text->lines( $core->lang['offer_edit_t'] ) ),
	            array('type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['name'], 'value' => $offer['offer_name']),
	            array('type' => 'text', 'length' => 200, 'name' => 'descr', 'head' => $core->lang['offer_descr'], 'descr' => $core->lang['offer_descr_d'], 'value' => $offer['offer_descr']),
	            array('type' => 'textarea', 'rows' => 4, 'name' => 'text', 'head' => $core->lang['offer_text'], 'descr' => $core->lang['offer_text_d'], 'value' => $offer['offer_text']),
	            array('type' => 'mces', 'name' => 'info', 'head' => $core->lang['offer_info'], 'descr' => $core->lang['offer_info_d'], 'value' => $offer['offer_info']),
	            array('type' => 'text', 'length' => 100, 'name' => 'price', 'head' => $core->lang['price'], 'value' => $offer['offer_price']),
	            array('type' => 'file', 'name' => 'image', 'head' => $core->lang['logo'], 'descr' => 'JPEG 320x180px' ),
	            array('type' => 'text', 'length' => 100, 'name' => 'country', 'head' => $core->lang['offer_country'], 'descr' => $core->lang['offer_country_d'], 'value' => $offer['offer_country']),
				array('type' => 'checkbox', 'name' => 'active', 'head' => $core->lang['active'], 'descr' => $core->lang['offer_active_d'], 'checked' => $offer['offer_active'] ),
				array('type' => 'checkbox', 'name' => 'vars', 'head' => $core->lang['offer_vars'], 'descr' => $core->lang['offer_vars_d'], 'checked' => $offer['offer_vars'] ),
				array('type' => 'checkbox', 'name' => 'delivery', 'head' => $core->lang['offer_delivery'], 'descr' => $core->lang['offer_delivery_d'], 'checked' => $offer['offer_delivery'] ),
	            array('type' => 'select', 'name' => 'payment', 'head' => $core->lang['offer_payment'], 'descr' => $core->lang['offer_payment_d'], 'value' => $payment ),
				array('type' => 'head', 'value' => $core->lang['offer_mr_h'] ),
				array('type' => 'checkbox', 'name' => 'mr', 'head' => $core->lang['offer_mr'], 'descr' => $core->lang['offer_mr_d'], 'checked' => $offer['offer_mr'] ),
	            array('type' => 'textarea', 'rows' => 4, 'name' => 'script', 'head' => $core->lang['offer_script'], 'descr' => $core->lang['offer_script_d'], 'value' => $offer['offer_script']),
		    );

		    foreach ( $comps as $i => $n ) {		    	$field[] = array( 'type' => 'text', 'name' => "mrt[$i]", 'head' => $n, 'descr' => $core->lang['offer_mrt_d'], 'value' => $mrt[$i] );
		    }

		    $button = array(array('type' => 'submit', 'value' => $core->lang['save']));
		    $core->form ('offeredit', $action, $method, $title, $field, $button);

	        $core->footer ();

	    } else {

	    	$offer = $core->db->data ( "SELECT * FROM ".DB_OFFER." ORDER BY offer_name ASC" );

		    $core->mainline->add ( $core->lang['offers_h'], $core->url('m', 'offer') );
		    $core->header ();

		    $core->tpl->load ('body', 'safelist');

		    $core->tpl->vars ('body', array (
		        'title'		    => $core->lang['offers_h'],
	            'text'			=> $core->text->lines ($core->lang['offers_t']),
		        'name'  	    => $core->lang['name'],
		        'info'			=> $core->lang['price'],
		        'action'    	=> $core->lang['action'],
	            'edit'			=> $core->lang['edit'],
	            'del'			=> $core->lang['del'],
	            'confirm'		=> $core->lang['confirms'],
		    ));

		    foreach ( $offer as &$i ) {		    	$act = $i['offer_active'] ? '<span class="icon sm rf isok"></span>' : '';
		        $core->tpl->block ('body', 'item', array (
		            'id'        => $i['offer_id'],
		            'name'		=> $i['offer_name'],
		            'more'		=> $act . '<small>'.rur( $i['offer_price'] ).'</small>',
		            'info'      => ( $i['offer_vars'] ? '<a href="'.$core->url( 'i', 'offer-vars', $i['offer_id'] ).'" class="variant">'.$core->lang['variants'].'</a>' : '' ) . ' <a href="'.$core->url( 'i', 'offer-price', $i['offer_id'] ).'" class="money">'.$core->lang['price'].'</a> <a href="'.$core->url( 'i', 'offer-pars', $i['offer_id'] ).'" class="param">'.$core->lang['params'].'</a>',
		            'url'       => $core->url ('i', 'offer-sites', $i['offer_id'] ),
		            'edit'      => $core->url ('i', 'offer', $i['offer_id'] ),
		            'del'       => $core->url ('a', 'offer-del', $i['offer_id'] ),
		        ));
		    } unset ($d);

			$core->tpl->output ('body');

		    $title	= $core->lang['offer_add_h'];
		    $action	= $core->url ( 'a', 'offer-add', 0 );
		    $method	= 'post';
		    $field 	= array(
		    	array('type' => 'line', 'value' => $core->text->lines( $core->lang['offer_add_t'] ) ),
	            array('type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['name'] ),
	            array('type' => 'text', 'length' => 32, 'name' => 'price', 'head' => $core->lang['price'] ),
		    );
		    $button = array(array('type' => 'submit', 'value' => $core->lang['create']));
		    $core->form ( 'offeradd', $action, $method, $title, $field, $button );

	        $core->footer ('admin');

	    }

	  	$core->_die ();

	  case 'offer-price':

	  	if ( ! $id ) $core->go($core->url( 'm', 'offer' ));
	  	$offer = $core->db->row( "SELECT * FROM ".DB_OFFER." WHERE offer_id = '$id' LIMIT 1" );
	  	$prices = unserialize( $offer['offer_prt'] );

	    $core->mainline->add ( $core->lang['offers_h'], $core->url('m', 'offer') );
	    $core->mainline->add ( sprintf ( $core->lang['offer_price_h'], $offer['offer_name'] ) );
	    $core->header ();

		$core->tpl->load( 'body', 'price' );

		$core->tpl->vars( 'body', array(
			'title'		=> sprintf ( $core->lang['offer_price_h'], $offer['offer_name'] ),
			'u_save'	=> $core->url( 'a', 'offer-price', $id ),
			'save'		=> $core->lang['save'],
		));

		$core->tpl->block( 'body', 'type', array( 'name' => $core->lang['offer_prices_base'] ) );

		$core->tpl->block( 'body', 'type.price', array(
			'name'	=> $core->lang['offer_price_main'],
			'wmn'	=> 'wmb',	'wmv'	=> $offer['offer_wm'] ? $offer['offer_wm'] : '',
			'wmun'	=> 'wmub',	'wmuv'	=> $offer['offer_wmu'] ? $offer['offer_wmu'] : '',
			'payn'	=> 'payb',	'payv'	=> $offer['offer_pay'] ? $offer['offer_pay'] : '',
			'pyun'	=> 'pyub',	'pyuv'	=> $offer['offer_pyu'] ? $offer['offer_pyu'] : '',
			'refn'	=> 'refb',	'refv'	=> $offer['offer_ref'] ? $offer['offer_ref'] : '',
		));  $core->tpl->block( 'body', 'type.price.ref' );

		$core->tpl->block( 'body', 'type.price', array(
			'name'	=> $core->lang['offer_price_vip'],
			'wmn'	=> 'wmv',	'wmv'	=> $offer['offer_wm_vip'] ? $offer['offer_wm_vip'] : '',
			'wmun'	=> 'wmuv',	'wmuv'	=> $offer['offer_wmu_vip'] ? $offer['offer_wmu_vip'] : '',
			'payn'	=> 'payv',	'payv'	=> $offer['offer_pay_vip'] ? $offer['offer_pay_vip'] : '',
			'pyun'	=> 'pyuv',	'pyuv'	=> $offer['offer_pyu_vip'] ? $offer['offer_pyu_vip'] : '',
			'refn'	=> 'refv',	'refv'	=> $offer['offer_ref_vip'] ? $offer['offer_ref_vip'] : '',
		));  $core->tpl->block( 'body', 'type.price.ref' );

		$core->tpl->block( 'body', 'type.price', array(
			'name'	=> $core->lang['offer_price_ext'],
			'cls'	=> 'dark',
			'wmn'	=> 'wme',	'wmv'	=> $offer['offer_wm_ext'] ? $offer['offer_wm_ext'] : '',
			'wmun'	=> 'wmue',	'wmuv'	=> $offer['offer_wmu_ext'] ? $offer['offer_wmu_ext'] : '',
			'payn'	=> 'paye',	'payv'	=> $offer['offer_pay_ext'] ? $offer['offer_pay_ext'] : '',
			'pyun'	=> 'pyue',	'pyuv'	=> $offer['offer_pyu_ext'] ? $offer['offer_pyu_ext'] : '',
		));

		$comps = $core->wmsale->get( 'comps' );
		if ( $comps ) {
			$core->tpl->block( 'body', 'type', array( 'name' => $core->lang['offer_prices_comp'] ) );
			foreach ( $comps as $i => $c ) {
				$uc = $core->wmsale->get( 'comp', $i, 'user_id' );
				$core->tpl->block( 'body', 'type.price', array(
					'name'	=> $c,
					'wmn'	=> "wm[$uc]",
					'wmv'	=> $prices[$uc][0] ? $prices[$uc][0] : '',
					'wmun'	=> "wmu[$uc]",
					'wmuv'	=> $prices[$uc][3] ? $prices[$uc][3] : '',
					'payn'	=> "pay[$uc]",
					'payv'	=> $prices[$uc][1] ? $prices[$uc][1] : '',
					'pyun'	=> "pyu[$uc]",
					'pyuv'	=> $prices[$uc][4] ? $prices[$uc][4] : '',
					'refn'	=> "ref[$uc]",
					'refv'	=> $prices[$uc][2] ? $prices[$uc][2] : '',
				));
				$core->tpl->block( 'body', 'type.price.ref' );
			}
		}

		$exts = $core->wmsale->get( 'exts' );
		if ( $exts ) {
			$core->tpl->block( 'body', 'type', array( 'name' => $core->lang['offer_prices_ext'] ) );
			foreach ( $exts as $i => $c ) {
				$uc = $core->wmsale->get( 'ext', $i, 'user_id' );
				$core->tpl->block( 'body', 'type.price', array(
					'name'	=> $c,
					'cls'	=> 'dark',
					'wmn'	=> "wm[$uc]",
					'wmv'	=> $prices[$uc][0] ? $prices[$uc][0] : '',
					'wmun'	=> "wmu[$uc]",
					'wmuv'	=> $prices[$uc][3] ? $prices[$uc][3] : '',
					'payn'	=> "pay[$uc]",
					'payv'	=> $prices[$uc][1] ? $prices[$uc][1] : '',
					'pyun'	=> "pyu[$uc]",
					'pyuv'	=> $prices[$uc][4] ? $prices[$uc][4] : '',
				));
			}
		}

		$refs = $core->db->icol( "SELECT user_id, user_name FROM ".DB_USER." WHERE user_work = -2 ORDER BY user_name ASC" );
		if ( $refs ) {
			$core->tpl->block( 'body', 'type', array( 'name' => $core->lang['offer_prices_ref'] ) );
			foreach ( $refs as $uc => $c ) {
				$core->tpl->block( 'body', 'type.price', array(
					'name'	=> $c,
					'wmn'	=> "wm[$uc]",
					'wmv'	=> $prices[$uc][0] ? $prices[$uc][0] : '',
					'wmun'	=> "wmu[$uc]",
					'wmuv'	=> $prices[$uc][3] ? $prices[$uc][3] : '',
					'payn'	=> "pay[$uc]",
					'payv'	=> $prices[$uc][1] ? $prices[$uc][1] : '',
					'pyun'	=> "pyu[$uc]",
					'pyuv'	=> $prices[$uc][4] ? $prices[$uc][4] : '',
					'refn'	=> "ref[$uc]",
					'refv'	=> $prices[$uc][2] ? $prices[$uc][2] : '',
				));
				$core->tpl->block( 'body', 'type.price.ref' );
			}
		}

		$vips = $core->db->icol( "SELECT user_id, user_name FROM ".DB_USER." WHERE user_vip = 1 AND user_work IN ( 0, 2 ) ORDER BY user_name ASC" );
		if ( $vips ) {
			$core->tpl->block( 'body', 'type', array( 'name' => $core->lang['offer_prices_vip'] ) );
			foreach ( $vips as $uc => $c ) {
				$core->tpl->block( 'body', 'type.price', array(
					'name'	=> $c,
					'wmn'	=> "wm[$uc]",
					'wmv'	=> $prices[$uc][0] ? $prices[$uc][0] : '',
					'wmun'	=> "wmu[$uc]",
					'wmuv'	=> $prices[$uc][3] ? $prices[$uc][3] : '',
					'payn'	=> "pay[$uc]",
					'payv'	=> $prices[$uc][1] ? $prices[$uc][1] : '',
					'pyun'	=> "pyu[$uc]",
					'pyuv'	=> $prices[$uc][4] ? $prices[$uc][4] : '',
					'refn'	=> "ref[$uc]",
					'refv'	=> $prices[$uc][2] ? $prices[$uc][2] : '',
				));
				$core->tpl->block( 'body', 'type.price.ref' );
			}
		}

		$core->tpl->output( 'body' );
		$core->footer();

	  	$core->_die();

	  case 'offer-pars':

	  	if ( ! $id ) $core->go($core->url( 'm', 'offer' ));
	  	$offer = $core->db->row( "SELECT * FROM ".DB_OFFER." WHERE offer_id = '$id' LIMIT 1" );
	  	$pars = unserialize( $offer['offer_pars'] );

	    $core->mainline->add ( $core->lang['offers_h'], $core->url('m', 'offer') );
	    $core->mainline->add ( sprintf ( $core->lang['offer_pars_h'], $offer['offer_name'] ) );
	    $core->header ();

		$core->tpl->load( 'body', 'param' );

		$core->tpl->vars( 'body', array(
			'title'		=> sprintf ( $core->lang['offer_pars_h'], $offer['offer_name'] ),
			'u_save'	=> $core->url( 'a', 'offer-param', $id ),
			'shave'		=> $pars['shave'] ? $pars['shave'] : '',
			'save'		=> $core->lang['save'],
		));

		$i = 1;
		foreach ( $pars as $k => $v ) if ( substr( $k, 0, 5 ) != 'shave' ) {			$core->tpl->block( 'body', 'param', array(
				'id'	=> $i,
				'name'	=> $k,
				'val'	=> $v ? $v : ''
			)); $i++;
		}

		$i = 111;
		$comps = $core->wmsale->get( 'comps' );
		foreach ( $comps as $n => $c ) {
			$core->tpl->block( 'body', 'shave', array(
				'id'	=> $i,
				'name'	=> $c,
				'param'	=> 'shave'.$n,
				'val'	=> $pars['shave'.$n] ? $pars['shave'.$n] : '',
			)); $i++;
		}

		$core->tpl->output( 'body' );
		$core->footer();

	  	$core->_die();

	  case 'offer-vars':

		switch ( $message ) {
	    	case 'edit-ok':	$core->info( 'info', 'done_offer_var_edit' ); break;
	    	case 'del-ok':	$core->info( 'info', 'done_offer_var_del' ); break;
	    	case 'add-e':	$core->info( 'error', 'error_offer_var_add' ); break;
	    	case 'edit-e':	$core->info( 'error', 'error_offer_var_edit' ); break;
	    	case 'del-e':	$core->info( 'error', 'error_offer_var_del' ); break;
		}

	  	if ( ! $id ) $core->go($core->url( 'm', 'offer' ));
	  	$offer = $core->db->row( "SELECT * FROM ".DB_OFFER." WHERE offer_id = '$id' LIMIT 1" );
	  	if ( ! $offer['offer_vars'] ) $core->go($core->url( 'm', 'offer' ));

    	$vars = $core->db->data ( "SELECT * FROM ".DB_VARS." WHERE offer_id = '$id' ORDER BY var_name ASC" );

	    $core->mainline->add ( $core->lang['offers_h'], $core->url('m', 'offer') );
	    $core->mainline->add ( sprintf ( $core->lang['offer_vars_h'], $offer['offer_name'] ) );
	    $core->header ();

	    $core->tpl->load ( 'body', 'list' );

	    $core->tpl->vars ('body', array (
	        'title'		    => sprintf ( $core->lang['offer_vars_h'], $offer['offer_name'] ),
            'text'			=> $core->text->lines ($core->lang['offer_vars_t']),
	        'name'  	    => $core->lang['name'],
	        'info'			=> $core->lang['price'],
	        'action'    	=> $core->lang['action'],
            'edit'			=> $core->lang['edit'],
            'del'			=> $core->lang['del'],
            'confirm'		=> $core->lang['confirm'],
	    ));

	    foreach ( $vars as &$i ) {
	        $core->tpl->block( 'body', 'item', array (
	            'id'        => $i['var_id'],
	            'name'		=> $i['var_name'],
	            'info'      => rur( $i['var_price'] ),
	            'url'       => $core->url( 'i', 'offer-var', $i['var_id'] ),
	            'edit'      => $core->url( 'i', 'offer-var', $i['var_id'] ),
	            'del'       => $core->url( 'a', 'offer-var-del', $i['var_id'] ),
	        ));
	    } unset( $d );

		$core->tpl->output( 'body' );

	    $title	= $core->lang['offer_var_add_h'];
	    $action	= $core->url( 'a', 'offer-var-add', $id );
	    $method	= 'post';
	    $field 	= array(
	    	array( 'type' => 'line', 'value' => $core->text->lines( $core->lang['offer_var_add_t'] ) ),
            array( 'type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['name'] ),
            array( 'type' => 'text', 'length' => 32, 'name' => 'price', 'head' => $core->lang['price'] ),
	    );
	    $button = array(array('type' => 'submit', 'value' => $core->lang['create'] ));
	    $core->form ( 'offervaradd', $action, $method, $title, $field, $button );

        $core->footer ();
		$core->_die ();

	  case 'offer-var':

		switch ( $message ) {
	    	case 'add-ok':	$core->info( 'info', 'done_offer_var_add' ); break;
	    	case 'add-e':	$core->info( 'error', 'error_offer_var_add' ); break;
		}

	  	if ( ! $id ) $core->go($core->url( 'm', 'offer' ));
	  	$vari = $core->db->row( "SELECT * FROM ".DB_VARS." WHERE var_id = '$id' LIMIT 1" );
	  	$offer = $core->db->row( "SELECT * FROM ".DB_OFFER." WHERE offer_id = '".$vari['offer_id']."' LIMIT 1" );

	    $core->mainline->add ( $core->lang['offers_h'], $core->url('m', 'offer') );
	    $core->mainline->add ( sprintf ( $core->lang['offer_vars_h'], $offer['offer_name'] ), $core->url('i', 'offer-vars', $offer['offer_id']) );
	    $core->mainline->add ( $vari['var_name'] );
	    $core->header ();

	    $title	= $core->lang['offer_var_edit_h'];
	    $action	= $core->url ( 'a', 'offer-var-edit', $id );
	    $method	= 'post';
	    $field 	= array(
	    	array('type' => 'line', 'value' => $core->text->lines( $core->lang['offer_var_edit_t'] ) ),
            array('type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['name'], 'value' => $vari['var_name']),
            array('type' => 'text', 'length' => 100, 'name' => 'short', 'head' => $core->lang['offer_short'], 'descr' => $core->lang['offer_short_d'], 'value' => $vari['var_short']),
            array('type' => 'text', 'length' => 10, 'name' => 'price', 'head' => $core->lang['price'], 'value' => $vari['var_price']),
	    );
	    $button = array(array('type' => 'submit', 'value' => $core->lang['save']));
	    $core->form ('offeredit', $action, $method, $title, $field, $button);

        $core->footer ();
		$core->_die ();

	  case 'offer-sites':

		switch ( $message ) {
	    	case 'edit-ok':	$core->info( 'info', 'done_offer_site_edit' ); break;
	    	case 'del-ok':	$core->info( 'info', 'done_offer_site_del' ); break;
	    	case 'add-e':	$core->info( 'error', 'error_offer_site_add' ); break;
	    	case 'edit-e':	$core->info( 'error', 'error_offer_site_edit' ); break;
	    	case 'del-e':	$core->info( 'error', 'error_offer_site_del' ); break;
		}

	  	if ( ! $id ) $core->go($core->url( 'm', 'offer' ));
	  	$offer = $core->wmsale->get( 'offer', $id );
	  	$comp = $core->wmsale->get( 'comps' );
    	$sites = $core->db->data ( "SELECT * FROM ".DB_SITE." WHERE offer_id = '$id' ORDER BY site_type, site_url ASC" );

	    $core->mainline->add ( $core->lang['offers_h'], $core->url('m', 'offer') );
	    $core->mainline->add ( sprintf ( $core->lang['offer_sites_h'], $offer['offer_name'] ) );
	    $core->header ();

	    $core->tpl->load ( 'body', 'list' );

	    $core->tpl->vars ('body', array (
	        'title'		    => sprintf ( $core->lang['offer_sites_h'], $offer['offer_name'] ),
            'text'			=> $core->text->lines (sprintf( $core->lang['offer_sites_t'], $core->url( 'a', 'offer-site-list', $id ), $core->url( 'a', 'offer-site-renew', $id ) )),
	        'name'  	    => $core->lang['name'],
	        'info'			=> $core->lang['company'],
	        'action'    	=> $core->lang['action'],
            'edit'			=> $core->lang['edit'],
            'del'			=> $core->lang['del'],
            'confirm'		=> $core->lang['confirm'],
	    ));

	    foreach ( $sites as &$i ) {
	    	$act = $i['site_default'] ? '<span class="icon sm rf isok"></span>' : '';
	        $core->tpl->block( 'body', 'item', array (
	            'id'        => $i['site_id'],
	            'name'		=> $i['site_url'],
	            'more'		=> $act . sprintf( '<small>(<a target="_blank" href="http://%s/">%s</a>)</small>', $i['site_url'], $core->lang['site_types'][$i['site_type']] ),
	            'info'      => $comp[$i['comp_id']],
	            'url'       => $core->url( 'i', 'offer-site', $i['site_id'] ),
	            'edit'      => $core->url( 'i', 'offer-site', $i['site_id'] ),
	            'del'       => $core->url( 'a', 'offer-site-del', $i['site_id'] ),
	        ));
	    } unset( $d );

		$core->tpl->output( 'body' );

	    $title	= $core->lang['offer_site_add_h'];
	    $action	= $core->url( 'a', 'offer-site-add', $id );
	    $method	= 'post';
	    $field 	= array(
	    	array( 'type' => 'line', 'value' => $core->text->lines( $core->lang['offer_site_add_t'] ) ),
            array( 'type' => 'text', 'length' => 100, 'name' => 'url', 'head' => $core->lang['offer_url'], 'descr' => $core->lang['offer_url_d'] ),
	    );
	    $button = array(array('type' => 'submit', 'value' => $core->lang['create'] ));
	    $core->form ( 'offersiteadd', $action, $method, $title, $field, $button );

        $core->footer ();
		$core->_die ();

	  case 'offer-site':

		switch ( $message ) {
	    	case 'add-ok':	$core->info( 'info', 'done_offer_site_add' ); break;
	    	case 'add-e':	$core->info( 'error', 'error_offer_site_add' ); break;
		}

	  	if ( ! $id ) $core->go($core->url( 'm', 'offer' ));
		$site = $core->wmsale->get( 'site', $id );
	  	$offer = $core->wmsale->get( 'offer', $site['offer_id'] );
	  	$comps = $core->wmsale->get( 'comps' );

	    $core->mainline->add ( $core->lang['offers_h'], $core->url('m', 'offer') );
	    $core->mainline->add ( sprintf ( $core->lang['offer_sites_h'], $offer['offer_name'] ), $core->url('i', 'offer-sites', $offer['offer_id']) );
	    $core->mainline->add ( $site['site_url'] );
	    $core->header ();

		$comp = array(); foreach ( $comps as $cv => $cn ) $comp[] = array( 'name' => $cn, 'value' => $cv, 'select' => ( $cv == $site['comp_id'] ) );
		$mobs = array(); foreach ( $core->lang['site_mobiles'] as $mi => $mv ) $mobs[] = array( 'name' => $mv, 'value' => $mi, 'select' => ( $mi == $site['site_mobile'] ) );

	    $title	= $core->lang['offer_site_edit_h'];
	    $action	= $core->url ( 'a', 'offer-site-edit', $id );
	    $method	= 'post';
	    $field 	= array(
	    	array('type' => 'line', 'value' => $core->text->lines( $core->lang['offer_site_edit_t'] ) ),
            array('type' => 'text', 'length' => 100, 'name' => 'url', 'head' => $core->lang['offer_url'], 'descr' => $core->lang['offer_url_d'], 'value' => $site['site_url']),
            array('type' => 'text', 'length' => 100, 'name' => 'key', 'head' => $core->lang['offer_key'], 'descr' => $core->lang['offer_key_d'], 'value' => $site['site_key']),
            array( 'type' => 'select', 'name' => 'comp', 'head' => $core->lang['company'], 'value' => $comp ),
            array( 'type' => 'select', 'name' => 'mobile', 'head' => $core->lang['site_mobile'], 'descr' => $core->lang['site_mobile_d'], 'value' => $mobs ),
            array( 'type' => 'checkbox', 'name' => 'type', 'head' => $core->lang['site_type'], 'descr' => $core->lang['site_type_d'], 'checked' => $site['site_type'] ),
            array( 'type' => 'checkbox', 'name' => 'default', 'head' => $core->lang['site_default'], 'descr' => $core->lang['site_default_d'], 'checked' => $site['site_default'] ),
            array( 'type' => 'checkbox', 'name' => 'comph', 'head' => $core->lang['site_comp'], 'descr' => $core->lang['site_comp_d'], 'checked' => $site['site_comp'] ),
	    );
	    $button = array(array('type' => 'submit', 'value' => $core->lang['save']));
	    $core->form ('offeredit', $action, $method, $title, $field, $button);

        $core->footer ();
		$core->_die ();

	  case 'integration':
		if ( $id ) {
		  	$comp = $core->wmsale->get( 'comp', $id );

		  	$flds = unserialize( $comp['int_add_field'] );
		  	$fld = ''; if ( $flds ) foreach ( $flds as $k => $v ) $fld .= "$k $v\n"; $fld = trim( $fld );
		  	$flds2 = unserialize( $comp['int_chk_field'] );
		  	$fld2 = ''; if ( $flds2 ) foreach ( $flds2 as $k => $v ) $fld2 .= "$k $v\n"; $fld2 = trim( $fld2 );
			$format = array(); foreach ( $core->lang['comp_int_formats'] as $v => $n ) $format[] = array( 'name' => $n, 'value' => $v, 'select' => $v == $comp['int_chk_format'] );

		    $core->mainline->add ( $core->lang['admin_comp_h'], $core->url('m', 'comps') );
			$core->mainline->add ( $comp['comp_name'] );
		    $core->header ();

		    $title	= $core->lang['comp_int_h'];
		    $action	= $core->url ( 'a', 'comps-int', $id );
		    $method	= 'post';
		    $field 	= array(
		    	array( 'type' => 'line', 'value' => $core->text->lines( $core->lang['comp_int_t'] ) ),
	            array( 'type' => 'checkbox', 'name' => 'add', 'head' => $core->lang['comp_int_add'], 'descr' => $core->lang['comp_int_add_d'], 'checked' => $comp['int_add'] ),
	            array('type' => 'text', 'length' => 200, 'name' => 'add_url', 'head' => $core->lang['comp_int_add_url'], 'descr' => $core->lang['comp_int_add_url_d'], 'value' => htmlspecialchars( $comp['int_add_url'] ) ),
	            array('type' => 'code', 'name' => 'add_pre', 'head' => $core->lang['comp_int_pre'], 'value' => $comp['int_add_pre'] ),
	            array('type' => 'textarea', 'rows' => 5, 'name' => 'add_field', 'head' => $core->lang['comp_int_add_field'], 'descr' => $core->lang['comp_int_add_field_d'], 'value' => $fld ),
	            array('type' => 'code', 'lang' => 'javascript', 'name' => 'add_code', 'head' => $core->lang['comp_int_add_code'], 'descr' => $core->lang['comp_int_add_code_d'], 'value' => $comp['int_add_code'] ),
	            array( 'type' => 'checkbox', 'name' => 'chk', 'head' => $core->lang['comp_int_chk'], 'descr' => $core->lang['comp_int_chk_d'], 'checked' => $comp['int_chk'] ),
	            array('type' => 'text', 'length' => 200, 'name' => 'chk_url', 'head' => $core->lang['comp_int_chk_url'], 'descr' => $core->lang['comp_int_chk_url_d'], 'value' => htmlspecialchars( $comp['int_chk_url'] ) ),
	            array('type' => 'code', 'name' => 'chk_pre', 'head' => $core->lang['comp_int_pre'], 'value' => $comp['int_chk_pre'] ),
	            array('type' => 'textarea', 'rows' => 5, 'name' => 'chk_field', 'head' => $core->lang['comp_int_chk_field'], 'descr' => $core->lang['comp_int_chk_field_d'], 'value' => $fld2 ),
	            array('type' => 'text', 'length' => 5, 'name' => 'chk_count', 'head' => $core->lang['comp_int_chk_count'], 'descr' => $core->lang['comp_int_chk_count_d'], 'value' => $comp['int_chk_count'] ),
	            array( 'type' => 'select', 'name' => 'chk_format', 'head' => $core->lang['comp_int_chk_format'], 'value' => $format ),
	            array('type' => 'code', 'lang' => 'javascript', 'name' => 'chk_code', 'head' => $core->lang['comp_int_chk_code'], 'descr' => $core->lang['comp_int_chk_code_d'], 'value' => $comp['int_chk_code'] ),
		    );
		    $button = array(array('type' => 'submit', 'value' => $core->lang['save']));
		    $core->form ('integrate', $action, $method, $title, $field, $button);

	        $core->footer ();
			$core->_die ();

		}

	  case 'comps':

		switch ( $message ) {
	    	case 'add-ok':	$core->info( 'info', 'done_comps_add' ); break;
	    	case 'edit-ok':	$core->info( 'info', 'done_comps_edit' ); break;
	    	case 'del-ok':	$core->info( 'info', 'done_comps_del' ); break;
	    	case 'add-e':	$core->info( 'error', 'error_comps_add' ); break;
	    	case 'edit-e':	$core->info( 'error', 'error_comps_edit' ); break;
	    	case 'del-e':	$core->info( 'error', 'error_comps_del' ); break;
	    	case 'del-a':	$core->info( 'error', 'error_comps_root' ); break;
		}

	  	if ( $id ) {

			$comp = $core->db->row ( "SELECT * FROM ".DB_COMP." WHERE comp_id = '$id' LIMIT 1" );

			$user = array(array( 'name' => '&mdash; ' .$core->lang['comp_free']. ' &mdash;', 'value' => 0 ));
			$users = $core->db->icol( "SELECT user_id, user_name FROM ".DB_USER." WHERE user_comp = '$id' AND user_compad = 1 ORDER BY user_name ASC" );
			foreach ( $users as $u => $n ) $user[] = array( 'name' => $n, 'value' => $u, 'select' => $u == $comp['user_id'] );

		    $core->mainline->add ( $core->lang['admin_comp_h'], $core->url('m', 'comps') );
			$core->mainline->add ( $comp['comp_name'] );
			$core->header ();

			$title	= $core->lang['comp_edit'];
			$action	= $core->url ( 'a', 'comps-edit', $id );
			$method	= 'post';
			$field 	= array(
				array( 'type' => 'line', 'value' => $core->text->lines( $core->lang['comp_info_t'] ) ),
				array( 'type' => 'select', 'name' => 'user', 'head' => $core->lang['comp_user'], 'descr' => $core->lang['comp_user_d'], 'value' => $user ),
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
				array( 'type' => 'head', 'value' => $core->lang['comp_pays'] ),
				array( 'type' => 'mces', 'name' => 'pay_info', 'head' => $core->lang['comp_pay'], 'descr' => $core->lang['comp_pay_d'], 'value' => $comp['pay_info']),
				array( 'type' => 'text', 'length' => 13, 'name' => 'pay_wmr', 'head' => $core->lang['comp_wmr'], 'value' => $comp['pay_wmr']),
				array( 'type' => 'text', 'length' => 64, 'name' => 'pay_wmk', 'head' => $core->lang['comp_wmk'], 'value' => $comp['pay_wmk']),
				array( 'type' => 'text', 'length' => 64, 'name' => 'pay_ymr', 'head' => $core->lang['comp_ymr'], 'value' => $comp['pay_ymr']),
				array( 'type' => 'text', 'length' => 64, 'name' => 'pay_ymk', 'head' => $core->lang['comp_ymk'], 'value' => $comp['pay_ymk']),
			);
			$button = array(array('type' => 'submit', 'value' => $core->lang['save']));
			$core->form ('comp', $action, $method, $title, $field, $button);

	        $core->footer ();

	    } else {

	    	$comps = $core->db->data( "SELECT comp_id, comp_name, comp_vip, user_id FROM ".DB_COMP." ORDER BY comp_name ASC" );

		    $core->mainline->add ( $core->lang['admin_comp_h'], $core->url('m', 'comps') );
		    $core->header ();

		    $core->tpl->load ('body', 'safelist');

		    $core->tpl->vars ('body', array (
		        'title'		    => $core->lang['admin_comp_h'],
	            'text'			=> $core->text->lines ($core->lang['admin_comp_t']),
		        'name'  	    => $core->lang['name'],
		        'info'			=> $core->lang['cash'],
		        'action'    	=> $core->lang['action'],
	            'edit'			=> $core->lang['edit'],
	            'del'			=> $core->lang['del'],
	            'confirm'		=> $core->lang['confirms'],
		    ));

		    foreach ( $comps as &$i ) {

		    	$cash = $i['user_id'] ? rur( $core->user->get( $i['user_id'], 'user_cash' ) ) : $core->lang['comp_free'];

		        $core->tpl->block ('body', 'item', array (
		            'id'        => $i['comp_id'],
		            'name'		=> $i['comp_name'],
		            'more'		=> ( $i['comp_vip'] ? $core->lang['iamvip'] : '' ) . ' <small>(<a href="'.$core->url( 'i', 'integration', $i['comp_id'] ).'">'.$core->lang['comp_int'].'</a>)</small>',
		            'info'      => $cash,
		            'url'       => $core->url ('i', 'comps', $i['comp_id'] ),
		            'edit'      => $core->url ('i', 'comps', $i['comp_id'] ),
		            'del'       => $core->url ('a', 'comps-del', $i['comp_id'] ),
		        ));

		    } unset ($d);

			$core->tpl->output ('body');

		    $title	= $core->lang['comp_add'];
		    $action	= $core->url ( 'a', 'comps-add', '' );
		    $method	= 'post';
		    $field 	= array(
	            array('type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['name'], 'descr' => $core->lang['comp_add_d'] ),
		    );
		    $button = array(array('type' => 'submit', 'value' => $core->lang['create']));
		    $core->form ( 'compadd', $action, $method, $title, $field, $button );

	        $core->footer ('admin');

	    }

	  	$core->_die ();

	  case 'ext':

		switch ( $message ) {
	    	case 'add-ok':	$core->info( 'info', 'done_add' ); break;
	    	case 'edit-ok':	$core->info( 'info', 'done_edit' ); break;
	    	case 'del-ok':	$core->info( 'info', 'done_del' ); break;
	    	case 'add-e':	$core->info( 'error', 'error_add' ); break;
	    	case 'edit-e':	$core->info( 'error', 'error_edit' ); break;
	    	case 'del-e':	$core->info( 'error', 'error_del' ); break;
		}

	  	if ( $id ) {

			$ext = $core->db->row ( "SELECT * FROM ".DB_EXT." WHERE ext_id = '$id' LIMIT 1" );

			$user = array(array( 'name' => '&mdash; ' .$core->lang['comp_free']. ' &mdash;', 'value' => 0 ));
			$users = $core->db->icol( "SELECT user_id, user_name FROM ".DB_USER." WHERE user_ext = '$id' ORDER BY user_name ASC" );
			foreach ( $users as $u => $n ) $user[] = array( 'name' => $n, 'value' => $u, 'select' => $u == $ext['user_id'] );

		    $core->mainline->add ( $core->lang['admin_ext_h'], $core->url('m', 'ext') );
			$core->mainline->add ( $ext['ext_name'] );
			$core->header ();

			$title	= $core->lang['ext_edit'];
			$action	= $core->url ( 'a', 'ext-edit', $id );
			$method	= 'post';
			$field 	= array(
				array( 'type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['name'], 'value' => $ext['ext_name']),
				array( 'type' => 'text', 'length' => 100, 'name' => 'key', 'head' => $core->lang['ext_key'], 'value' => $ext['ext_key']),
				array( 'type' => 'select', 'name' => 'user', 'head' => $core->lang['ext_user'], 'descr' => $core->lang['ext_user_d'], 'value' => $user ),
				array( 'type' => 'head', 'value' => $core->lang['ext_url'] ),
				array( 'type' => 'line', 'value' => $core->lang['ext_url_d'] ),
				array( 'type' => 'text', 'length' => 200, 'name' => 'url_new', 'head' => $core->lang['ext_new'], 'value' => $ext['url_new']),
				array( 'type' => 'text', 'length' => 200, 'name' => 'url_nc', 'head' => $core->lang['ext_nc'], 'value' => $ext['url_nc']),
				array( 'type' => 'text', 'length' => 200, 'name' => 'url_rc', 'head' => $core->lang['ext_rc'], 'value' => $ext['url_rc']),
				array( 'type' => 'text', 'length' => 200, 'name' => 'url_acc', 'head' => $core->lang['ext_acc'], 'value' => $ext['url_acc']),
				array( 'type' => 'text', 'length' => 200, 'name' => 'url_dec', 'head' => $core->lang['ext_dec'], 'value' => $ext['url_dec']),
				array( 'type' => 'text', 'length' => 200, 'name' => 'url_pay', 'head' => $core->lang['ext_pay'], 'value' => $ext['url_pay']),
				array( 'type' => 'text', 'length' => 200, 'name' => 'url_ret', 'head' => $core->lang['ext_ret'], 'value' => $ext['url_ret']),
				array( 'type' => 'text', 'length' => 200, 'name' => 'url_del', 'head' => $core->lang['ext_del'], 'value' => $ext['url_del']),
				array( 'type' => 'head', 'value' => $core->lang['ext_code'] ),
				array( 'type' => 'code', 'lang' => 'php', 'name' => 'code_offer', 'head' => $core->lang['ext_code_offer'], 'value' => $ext['code_offer'] ),
				array( 'type' => 'code', 'lang' => 'php', 'name' => 'code_accept', 'head' => $core->lang['ext_code_accept'], 'value' => $ext['code_accept'] ),

			);
			$button = array(array('type' => 'submit', 'value' => $core->lang['save']));
			$core->form ('ext', $action, $method, $title, $field, $button);

	        $core->footer ();

	    } else {

	    	$exts = $core->db->data( "SELECT ext_id, ext_name, user_id FROM ".DB_EXT." ORDER BY ext_name ASC" );

		    $core->mainline->add ( $core->lang['admin_ext_h'], $core->url('m', 'ext') );
		    $core->header ();

		    $core->tpl->load ('body', 'safelist');

		    $core->tpl->vars ('body', array (
		        'title'		    => $core->lang['admin_ext_h'],
	            'text'			=> $core->text->lines ($core->lang['admin_ext_t']),
		        'name'  	    => $core->lang['name'],
		        'info'			=> $core->lang['cash'],
		        'action'    	=> $core->lang['action'],
	            'edit'			=> $core->lang['edit'],
	            'del'			=> $core->lang['del'],
	            'confirm'		=> $core->lang['confirms'],
		    ));

		    foreach ( $exts as &$i ) {

		    	$cash = $i['user_id'] ? rur( $core->user->get( $i['user_id'], 'user_cash' ) ) : $core->lang['ext_free'];

		        $core->tpl->block ('body', 'item', array (
		            'id'        => $i['ext_id'],
		            'name'		=> $i['ext_name'],
		            'info'      => $cash,
		            'url'       => $core->url ('i', 'ext', $i['ext_id'] ),
		            'edit'      => $core->url ('i', 'ext', $i['ext_id'] ),
		            'del'       => $core->url ('a', 'ext-del', $i['ext_id'] ),
		        ));

		    } unset ($d);

			$core->tpl->output ('body');

		    $title	= $core->lang['ext_add'];
		    $action	= $core->url ( 'a', 'ext-add', '' );
		    $method	= 'post';
		    $field 	= array(
	            array('type' => 'text', 'length' => 100, 'name' => 'name', 'head' => $core->lang['name'], 'descr' => $core->lang['ext_add_d'] ),
		    );
		    $button = array(array('type' => 'submit', 'value' => $core->lang['create']));
		    $core->form ( 'extadd', $action, $method, $title, $field, $button );

	        $core->footer();

	    }

	  	$core->_die ();

	  // Money-Out Operations
	  case 'outs':

		switch ( $message ) {
	    	case 'acc-ok':	$core->info( 'info', 'done_out_acc' ); break;
	    	case 'dec-ok':	$core->info( 'info', 'done_out_dec' ); break;
	    	case 'acc-e':	$core->info( 'error', 'error_out_acc' ); break;
	    	case 'dec-e':	$core->info( 'error', 'error_out_dec' ); break;
		}

	   	$trs = $core->db->data ( "SELECT * FROM ".DB_CASH."  WHERE cash_type = 4 ORDER BY user_id ASC, cash_time DESC");
		if ( count( $trs ) ) {
			$ui = $s = array();
			foreach ( $trs as &$t ) {				$ui[] = $t['user_id'];
				$s[$t['user_id']] += $t['cash_value'];
			} unset ( $t );
			$ui = implode( ',', array_unique( $ui ) );
			$u = $core->db->icol( "SELECT user_id, user_name FROM ".DB_USER." WHERE user_id IN ( $ui )" );
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

				if ( $ou != $c['user_id'] ) {					$ou = $c['user_id'];
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

	  //
	  // News
	  //

	  case 'news-add':

	    $core->mainline->add ( $core->lang['news'], $core->url('m', 'news') );
		$core->mainline->add ( $core->lang['news_add_h'] );
		$core->header ();

		$groups = array(); foreach ( $core->lang['news_groups'] as $v => $nm ) $groups[] = array( 'name' => $nm, 'value' => $v );

	    $title	= $core->lang['news_add_h'];
	    $action	= $core->url ( 'a', 'news-add', '' );
	    $method	= 'post';
	    $field 	= array(
	    	array('type' => 'line', 'value' => $core->text->lines( $core->lang['news_t'] ) ),
            array('type' => 'text', 'length' => 100, 'name' => 'title', 'head' => $core->lang['title'] ),
            array('type' => 'mcea', 'name' => 'text', 'head' => $core->lang['text'] ),
            array('type' => 'select', 'name' => 'group', 'head' => $core->lang['news_group'], 'value' => $groups ),
			array('type' => 'checkbox', 'name' => 'vip', 'head' => $core->lang['news_vip'], 'descr' => $core->lang['news_vip_d'] ),
			array('type' => 'checkbox', 'name' => 'send', 'head' => $core->lang['news_send'], 'descr' => $core->lang['news_send_d'] ),
	    );
	    $button = array(array('type' => 'submit', 'value' => $core->lang['create']));
	    $core->form ( 'news', $action, $method, $title, $field, $button );

	    $core->footer ();
	  	$core->_die ();

	  case 'news':

		if ( $id ) {
			$n = $core->db->row( "SELECT * FROM ".DB_NEWS." WHERE news_id = '$id' LIMIT 1" );
		    $core->mainline->add ( $core->lang['news'], $core->url('m', 'news') );
			$core->mainline->add ( $core->lang['news_edit_h'] );
			$core->header ();

			$groups = array(); foreach ( $core->lang['news_groups'] as $v => $nm ) $groups[] = array( 'name' => $nm, 'value' => $v, 'select' => $v == $n['news_group'] );

		    $title	= $core->lang['news_edit_h'];
		    $action	= $core->url ( 'a', 'news-edit', $id );
		    $method	= 'post';
		    $field 	= array(
		    	array('type' => 'line', 'value' => $core->text->lines( $core->lang['news_t'] ) ),
	            array('type' => 'text', 'length' => 100, 'name' => 'title', 'head' => $core->lang['title'], 'value' => $n['news_title'] ),
	            array('type' => 'mcea', 'name' => 'text', 'head' => $core->lang['text'], 'value' => $n['news_text'] ),
	            array('type' => 'select', 'name' => 'group', 'head' => $core->lang['news_group'], 'value' => $groups ),
				array('type' => 'checkbox', 'name' => 'vip', 'head' => $core->lang['news_vip'], 'descr' => $core->lang['news_vip_d'], 'checked' => $n['news_vip'] ),
				array('type' => 'checkbox', 'name' => 'send', 'head' => $core->lang['news_send'], 'descr' => $core->lang['news_send_d'] ),
		    );
		    $button = array(array('type' => 'submit', 'value' => $core->lang['save']));
		    $core->form ( 'news', $action, $method, $title, $field, $button );

		    $core->footer ();
		  	$core->_die ();

		} break;

	  case 'support':

		if ( $id ) {
			require_once PATH_LIB . 'support.php';

			$user = $core->user->get( $id );
			$core->mainline->add( $core->lang['support'], $core->url( 'm', 'support' ) );
			$core->mainline->add( $user['user_name'] );
		    $core->header ();

			$core->tpl->load( 'body', 'message' );

			$core->tpl->vars( 'body', array(
	        	'title'			=> $core->lang['support'],
				'add'			=> $core->lang['send'],
	        	'nomessage1'	=> $core->lang['support_nm1'],
	        	'nomessage2'	=> $core->lang['support_nm2'],
				'showmore'		=> $core->lang['support_more'],
				'placeholder'	=> $core->lang['support_ph_admin'],
				'u_load'		=> $core->url( 'a', 'supp-show', $id ),
				'u_add'			=> $core->url( 'a', 'supp-add', $id ),
				'mc'			=> 0
	        ));

			$core->tpl->block( 'body', 'face' );

			$mn = $mx = 0;
			$messages = support_show( $core, $id, 1, 0 );
			if ( $mc = count( $messages ) ) {
				foreach ( $messages as &$m ) {
					$core->tpl->block( 'body', 'msg', $m );
					if ( $m['uid'] == $id ) $core->tpl->block( 'body', 'msg.admin', array( 'u' => $user['user_mail'] ) );
					$mx = max( $mx, $m['id'] );
					$mn = $mn ? min( $mn, $m['id'] ) : $m['id'];
				} unset ( $m );
				$core->tpl->block( 'body', 'more' );
			} else $core->tpl->block( 'body', 'face.nomsg' );
			$core->tpl->vars( 'body', array( 'mn' => $mn, 'mx' => $mx ));

			$core->tpl->output( 'body' );

		    $core->footer ();

		} else {
			$core->mainline->add( $core->lang['support_h'], $core->url('m', 'support') );
			$core->header();

			$show = 30; $start = ( $page - 1 ) * $show;
			$supp = $core->db->data ( "SELECT * FROM ".DB_USER." WHERE supp_last != 0 ORDER BY supp_last DESC LIMIT $start, $show" );
			$sc = $core->db->field( "SELECT COUNT(*) FROM ".DB_USER." WHERE supp_last != 0" );

			$core->tpl->load ( 'body', 'support' );

			$core->tpl->vars ('body', array (
				'title'		=> $core->lang['support_h'],
				'text'		=> $core->text->lines ($core->lang['support_t']),
				'name'		=> $core->lang['name'],
				'url'		=> $core->lang['url'],
				'action'	=> $core->lang['action'],
				'status'	=> $core->lang['status'],
				'time'		=> $core->lang['time'],
				'user'		=> $core->lang['user'],
				'view'		=> $core->lang['view'],
			));

			foreach ( $supp as &$s ) {
				$core->tpl->block ('body', 'supp', array(
					'link'		=> $core->url ( 'i', 'support', $s['user_id'] ),
					'id'		=> $s['user_id'],
					'time'		=> smartdate( $s['supp_last'] ),
					'name'		=> $s['user_name'],
					'user'		=> $s['supp_name'],
					'status'	=> $s['supp_admin'] ? sprintf( $core->lang['support_new'], $s['supp_admin'] ) : ( $s['supp_type'] ? ( $s['supp_new'] ? $core->lang['support_ur'] : $core->lang['support_ok'] ) : $core->lang['support_ua'] ),
					'uclass'	=> $s['supp_type'] ? 'user-alt' : 'user-blue',
					'sclass'	=> $s['supp_admin'] ? 'new' : ( $s['supp_type'] ? ( $s['supp_new'] ? 'ur' : 'ok' ) : 'ua' ),
					'vclass'	=> $s['supp_admin'] ? 'new' : 'no',
				));
			} unset ( $supp, $s );

			$core->tpl->output ('body');

			$core->footer();

		} $core->_die ();

	  case 'business':

		if ( ! $id ) $id = date( 'Ym' );
		if ( $id < 10000 ) $id *= 100;
		$year = round( $id / 100 );
		$month = $id % 100;

		if ( $month ) {
			$f = strtotime( "$year-$month-01" );
			$e = strtotime( "+ 1 month", $f );
		} else {
			$f = strtotime( "$year-01-01" );
			$e = strtotime( "+ 1 year", $f );
		}

		$balance = array();
		$money = $core->db->icol( "SELECT cash_type, SUM(cash_value) FROM ".DB_CASH." WHERE cash_time BETWEEN '$f' AND '$e' GROUP BY cash_type" );
		foreach ( $core->lang['cash_type'] as $i => &$v ) $balance[$i] = $money[$i] * $core->lang['cash_balance'][$i];

		$debt = $core->db->icol( "SELECT user_name, user_cash FROM ".DB_USER." WHERE user_work = 1 AND user_cash < 0 ORDER BY user_cash DESC" );
		$cred = $core->db->icol( "SELECT user_name, user_cash FROM ".DB_USER." WHERE user_work IN ( 0, 2 )AND user_cash > 0 ORDER BY user_cash ASC" );
		$exts = $core->db->icol( "SELECT user_name, user_cash FROM ".DB_USER." WHERE user_work = -1 AND user_cash > 0 ORDER BY user_cash ASC" );

		$core->mainline->add( $core->lang['menu_business'] );
		$core->header();

		$core->tpl->load( 'body', 'business' );

		$core->tpl->vars( 'body', array(
			'u_trans'		=> $core->url( 'm', 'trans' ),
			'trans'			=> $core->lang['menu_trans'],
			'years'			=> $core->lang['year'],
			'months'		=> $core->lang['month'],
			'cat'			=> $core->lang['business_cat'],
			'total'			=> $core->lang['business_total'],
			'summ'			=> $core->lang['cash'],
			'balance'		=> $core->lang['business_balance'],
			'm_balance'		=> rur( array_sum( $balance ) ),
			'user'			=> $core->lang['user'],
			'debt'			=> $core->lang['debt_list'],
			'cred'			=> $core->lang['cred_list'],
			'nodebts'		=> $core->lang['debt_no'],
			'nocreds'		=> $core->lang['cred_no'],
			'cred_balance'	=> $core->lang['cred_balance'],
			'cred_wait'		=> $core->lang['cred_wait'],
			'd_balance'		=> rur( abs( $dt = array_sum( $debt ) ) ),
			'c_balance'		=> rur( $ct = array_sum( $cred ) ),
			'c_ext'			=> rur( $et = array_sum( $exts ) ),
			'c_wait'		=> rur( abs( $money[4] ) ),
			'c_total'		=> rur( $ct + $et + abs( $money[4] ) ),
		));

		if ( $dt ) $core->tpl->block( 'body', 'dt' );
		if ( $ct ) $core->tpl->block( 'body', 'ct' );
		if ( $et ) $core->tpl->block( 'body', 'et' );
		if ( $money[4] ) $core->tpl->block( 'body', 'morecred' );

		for ( $y = 2014; $y <= date('Y'); $y++ ) $core->tpl->block( 'body', 'year', array( 'class' => ( $y == $year ) ? 'current' : '', 'mclass' => ( $y == $year ) ? 'primary' : 'info', 'url' => $core->url( 'i', 'business', $y ), 'text' => $y ));
		for ( $m = 1; $m < 13; $m++ ) $core->tpl->block( 'body', 'month', array( 'class' => ( $m == $month ) ? 'current' : '', 'mclass' => ( $m == $month ) ? 'primary' : 'info', 'url' => $core->url( 'i', 'business', sprintf( "%04d%02d", $year, $m ) ), 'text' => $core->lang['months'][$m] ));

		foreach ( $core->lang['cash_type'] as $i => &$v ) {
			$core->tpl->block( 'body', 'cash', array(
				'id'		=> $i,
				'name'		=> $v,
				'summ'		=> rur( $money[$i] ),
				'balance'	=> rur( $balance[$i] ),
			));
		} unset ( $v );

		if (count( $debt )) foreach ( $debt as $n => $s ) {
			$core->tpl->block( 'body', 'debt', array(
				'name'	=> $n,
				'summ'	=> rur(abs( $s )),
			));
		} else $core->tpl->block( 'body', 'nodebt' );

		if (count( $cred )) foreach ( $cred as $n => $s ) {
			$core->tpl->block( 'body', 'cred', array(
				'name'	=> $n,
				'summ'	=> rur(abs( $s )),
			));
		} elseif ( ! $money[4] ) $core->tpl->block( 'body', 'nocred' );

		if (count( $exts )) foreach ( $exts as $n => $s ) {
			$core->tpl->block( 'body', 'ext', array(
				'name'	=> $n,
				'summ'	=> rur(abs( $s )),
			));
		} elseif ( ! $money[4] ) $core->tpl->block( 'body', 'noext' );

		$core->tpl->output( 'body' );

	    $title	= $core->lang['exit_h'];
	    $action	= $core->url ( 'a', 'saw', 0 );
	    $method	= 'post';
	    $field 	= array(
	    	array('type' => 'line', 'value' => $core->text->lines($core->lang['exit_t']) ),
			array('type' => 'text', 'length' => 6, 'name' => 'sum', 'head' => $core->lang['exit_sum'], 'descr' => sprintf( $core->lang['exit_sum_d'], rur(array_sum( $balance )) ), 'value' => $catname ),
	    );

	    $users = $core->db->data( "SELECT user_id, user_name, user_mail FROM ".DB_USER." WHERE user_level = 1 ORDER BY user_name ASC" );
	    foreach ( $users as $u ) $field[] = array( 'type' => 'checkbox', 'name' => 'user[]', 'value' => $u['user_id'], 'head' => $u['user_name'], 'descr' => $u['user_mail'], 'checked' => 1 );

	    $button = array( array('type' => 'submit', 'value' => $core->lang['exit_process']) );
	    $core->form ( 'chainsaw', $action, $method, $title, $field, $button );

		$core->footer();
		$core->_die();

	  case 'trans':

		switch ( $message ) {
	    	case 'del':		$core->info( 'info', 'trans_del' ); break;
		}

		$where = array();

		// Search
		if ( isset( $core->get['s'] ) && $core->get['s'] ) {
			require_once PATH_CORE . 'search.php';
			$search = new SearchWords( $core->get['s'] );
			if ( $s = $search->get() ) {
				$where[] = $search->field(array( 'cash_descr' ));
			} else $s = false;
		} else $s = false;

		// Date filtering
		if ( $d = $core->get['d'] ) {
			$dd = explode( '-', $d );
			$ds = mktime( 0, 0, 0, $dd[1], $dd[2], $dd[0] );
			$de = mktime( 23, 59, 59, $dd[1], $dd[2], $dd[0] );
			$where[] = "( cash_time BETWEEN '$ds' AND '$de' )";
		} else $d = false;

		// User filtering
		if ( isset( $core->get['f'] ) && $core->get['f'] != '' ) {
			$f = (int) $core->get['f'];
			$where[] = "user_id = '$f'";
		} else $f = false;

		// Type filtering
		if ( isset( $core->get['t'] ) && $core->get['t'] != '' ) {
			$t = (int) $core->get['t'];
			$where[] = "cash_type = '$t'";
		} else $t = false;

		$where = count( $where ) ? implode( ' AND ', $where ) : '1';

	    $sh 	= 50; $st = $sh * ($page-1);
	   	$trc 	= $core->db->field ( "SELECT COUNT(*) FROM ".DB_CASH." WHERE $where" );
	   	$trs 	= $trc ? $core->db->data ( "SELECT * FROM ".DB_CASH." WHERE $where ORDER BY cash_time DESC LIMIT $st, $sh") : array();

		if ( $trc ) {
			$ui = array(); foreach ( $trs as &$tq ) $ui[] = $tq['user_id']; unset ( $tq );
			$ui = implode( ',', array_unique( $ui ) );
			$u = $core->db->icol( "SELECT user_id, user_name FROM ".DB_USER." WHERE user_id IN ( $ui )" );
		} else $u = array();

		$core->mainline->add( $core->lang['menu_trans'], $core->url( 'm', 'trans' ) );
	    $core->header ();

		$core->tpl->load( 'body', 'trans' );

	    $core->tpl->vars ('body', array (

			'user'			=> $core->lang['user'],
			'type'			=> $core->lang['type'],
			'cash'			=> $core->lang['cash'],
			'status'		=> $core->lang['status'],
			'time'			=> $core->lang['time'],
			'del'			=> $core->lang['del'],
			'confirm'		=> $core->lang['confirm'],

			'd'				=> $d,
			'f'				=> $f,
			's'				=> $search ? $search->get() : $s,
			'pages'			=> pages ( $core->url( 'm', 'trans?' ) . ( $f ? 'f='.$f.'&' : '' ) . ( $d ? 'd='.$d.'&' : '' ) . ( $t ? 't='.$t.'&' : '' ) . ( $s ? 's='.$s.'&' : '' ), $trc, $sh, $page ),
			'shown'			=> sprintf( $core->lang['shown'], $st+1, min( $st+$sh, $trc ), $trc ),
			'filter'		=> $core->lang['filter'],
			'date'			=> $core->lang['date'],
			'search'		=> $core->lang['search'],
			'find'			=> $core->lang['find'],

	    ));

	    if ( $f ) {	    	$core->tpl->block( 'body', 'user' );
	    	$core->tpl->vars( 'body', array(
				'user'	=> $core->lang['user'],
				'u'		=> $u[$f],
				'reset'	=> $core->url( 'm', 'trans?' ) . ( $d ? 'd='.$d.'&' : '' ) . ( $s ? 's='.$s.'&' : '' ),
	    	));
	    }

		foreach ( $core->lang['cash_type'] as $i => $st ) {
			$core->tpl->block( 'body', 'type', array(
				'name'		=> $st,
				'value'		=> $i,
				'select'	=> ( $t == $i ) ? 'selected="selected"' : '',
			));
		}

		if ( count( $trs ) ) {
			foreach ( $trs as &$c ) {
				$core->tpl->block( 'body', 'fin', array(
					'user'		=> $u[$c['user_id']],
					'uu'		=> $core->url( 'm', 'trans' ) . '?f=' . $c['user_id'],
					'type'		=> $core->lang['cash_type'][$c['cash_type']],
					'tid'		=> $c['cash_type'],
					'descr'		=> $c['cash_descr'] ? '('.( $search ? $search->highlight( $c['cash_descr'] ) : $c['cash_descr'] ).')' : '',
					'value'		=> rur( $c['cash_value'] ),
		            'del'		=> $core->url ('a', 'trans-del', $c['cash_id'] ),
		            'time'		=> smartdate( $c['cash_time'] ),
				));
			} unset ( $t, $trs );
		} else $core->tpl->block( 'body', 'nofin', array() );

		$core->tpl->output( 'body' );

	    $core->footer ();
	  	$core->_die ();

	  case 'analytics':

		$today	= date( 'Ymd' );
		$yest	= date( 'Ymd', strtotime( '-1 day' ) );
		$day7	= date( 'Ymd', strtotime( '-7 days' ) );
		$day30	= date( 'Ymd', strtotime( '-30 days' ) );

		if ( isset( $core->get['to'] ) && $core->get['to'] ) {
			$to = form2date( $core->get['to'] );
			if ( $to > $today ) $to = $today;
		} else $to = $today;

		if ( isset( $core->get['from'] ) && $core->get['from'] ) {
			$from = form2date( $core->get['from'] );
			if ( $from > $to ) $from = $to;
		} else $from = $today;

		$ff = strtotime( date2form( $from ) . ' 00:00:00' );
		$tt = strtotime( date2form( $to ) . ' 23:59:59' );
		$where = array( "( order_time BETWEEN '$ff' AND '$tt' )" );

		if ( isset( $core->get['o'] ) && $core->get['o'] ) {
			$o = (int) $core->get['o'];
			$where[] = "offer_id = '$o'";
		} else $o = false;

		if ( isset( $core->get['c'] ) && $core->get['c'] ) {
			$c = (int) $core->get['c'];
			$where[] = "comp_id = '$c'";
		} else $c = false;

		if ( isset( $core->get['a'] ) && $core->get['a'] ) {
			$a = true;
		} else $a = false;

		$where = implode ( ' AND ', $where );

		$offers = $core->wmsale->get( 'offers' );
		$comps = $core->wmsale->get( 'comps' );

		$comp = $user = $site = $flow = $ext = $offer = $man = $uf = $os = $cm = array();
		$total = $em = array( 'st0' => 0, 'st1' => 0, 'st2' => 0, 'st3' => 0, 'st4' => 0, 'st5' => 0, 'st6' => 0, 'st91' => 0, 'st92' => 0, 'st101' => 0, 'st102' => 0, 'st111' => 0, 'st112' => 0, 'st12' => 0, 'dc1' => 0, 'dc2' => 0, 'dc3' => 0, 'dc4' => 0, 'dc5' => 0, 'dc6' => 0, 'dc7' => 0, 'dc8' => 0, 'mi' => 0, 'mo' => 0, 'mt' => 0 );
		$oq = $core->db->start( "SELECT offer_id, comp_id, wm_id, ext_id, ext_src, site_id, user_id, flow_id, order_status, order_reason, order_shave, order_delivery, order_count FROM ".DB_ORDER." WHERE $where" );
		while ( $q = $core->db->one( $oq ) ) {

			// Processing stats
			$process = array( &$total );

			if ( $q['flow_id'] ) {
				$userid = $q['wm_id'];
				if (!isset( $flow[$q['flow_id']] )) $flow[$q['flow_id']] = $em; $process[] = &$flow[$q['flow_id']];
				$uf[$userid][] = $q['flow_id'];  $uf[$userid] = array_unique( $uf[$userid] );
			} elseif ( $q['ext_id'] ) {
				$userid = $q['wm_id'] ? $q['wm_id'] : (int) $core->wmsale->get( 'ext', $q['ext_id'], 'user_id' );
				if ( $userid && $q['ext_src'] ) {
					if (!isset( $ext[$userid] )) $ext[$userid] = array();
					if (!isset( $ext[$userid][$q['ext_src']] )) $ext[$userid][$q['ext_src']] = $em;
					$process[] = &$ext[$userid][$q['ext_src']];
				}
			} else $userid = 0;
			if ( ! $userid ) $userid = $q['wm_id'];

			if (!isset( $user[$userid] )) $user[$userid] = $em;
			$process[] = &$user[$userid];

			if (!isset( $comp[$q['comp_id']] )) $comp[$q['comp_id']] = $em; $process[] = &$comp[$q['comp_id']];
			if (!isset( $offer[$q['offer_id']] )) $offer[$q['offer_id']] = $em; $process[] = &$offer[$q['offer_id']];

			if ( ( $a || $o ) && $q['site_id'] ) {
				$os[$q['offer_id']][] = $q['site_id']; $os[$q['offer_id']] = array_unique( $os[$q['offer_id']] );
				if (!isset( $site[$q['site_id']] )) $site[$q['site_id']] = $em; $process[] = &$site[$q['site_id']];
			}

			if ( $c && $q['user_id'] ) {
				$cs[$q['comp_id']][] = $q['user_id']; $cs[$q['comp_id']] = array_unique( $cs[$q['comp_id']] );
				if (!isset( $man[$q['user_id']] )) $man[$q['user_id']] = $em; $process[] = &$man[$q['user_id']];
			}

			// Increments for stats
			$incs = array();
			if ( $q['order_status'] == 12 ) {
				$incs['st12'] = 1;
			} elseif ( $q['order_status'] > 5 ) {
				$incs['st6'] = 1;
				if ( $q['order_status'] == 8 ) $incs['st9'.$q['order_delivery']] = 1;
				if ( $q['order_status'] == 9 ) $incs['st9'.$q['order_delivery']] = 1;
				if ( $q['order_status'] == 10 ) $incs['st10'.$q['order_delivery']] = 1;
				if ( $q['order_status'] == 11 ) $incs['st11'.$q['order_delivery']] = 1;
				$cu = $core->wmsale->get( 'comp', $q['comp_id'], 'user_id' );
				$of = $core->wmsale->get( 'offer', $q['offer_id'] );
				$mn = $core->wmsale->price( $q['offer_id'], array( $userid, $cu ) );
				$incs['mo'] = $userid ? $mn['wmp'] : 0;
				$incs['mi'] = $cu ?  $mn['pay'] : 0;
				if ( $q['order_count'] > 1 ) {
					if ( $userid && $mn['wmu'] ) $incs['mo'] += $mn['wmu'] * ( $q['order_count'] - 1 );
					if ( $cu && $mn['pyu'] ) $incs['mi'] += $mn['pyu'] * ( $q['order_count'] - 1 );
				}
				if ( $q['order_shave'] ) {					$incs['mo'] = 0;
					if ( $q['order_shave'] == 1 ) $incs['mi'] = 0;
				}
				$incs['mt'] = $incs['mi'] - $incs['mo'];
			} elseif ( $q['order_status'] == 5 ) {
				$incs['st5'] = 1;
				$incs['dc'.$q['order_reason']] = 1;
			} else {
				$incs['st0'] = 1;
				$incs['st'.$q['order_status']] = 1;
			}

			// Increment all
			foreach ( $process as &$p ) {
				foreach ( $incs as $i => $v ) $p[$i] += $v;
			}

		} $core->db->stop( $oq );

		ksort ( $user ); ksort( $comp ); ksort( $offer ); ksort( $man ); ksort( $site ); ksort( $flow );

	    $core->mainline->add ( $core->lang['menu_analytics'], $core->url( 'm', 'analytics' ) );
	    $core->header ();

		$core->tpl->load( 'body', 'analytics' );

		$core->tpl->vars( 'body', array(

			'name'			=> $core->lang['name'],
			'user'			=> $core->lang['user'],
			'comp'			=> $core->lang['company'],
			'offer'			=> $core->lang['offer'],
			'show'			=> $core->lang['show'],
			'wait'			=> $core->lang['stat_wait'],
			'accept'		=> $core->lang['stat_accept'],
			'cancel'		=> $core->lang['stat_cancel'],
			'from'			=> date2form( $from ),
			'to'			=> date2form( $to ),

			'today'			=> $core->lang['anal_today'],
			'yest'			=> $core->lang['anal_yest'],
			'day7'			=> $core->lang['anal_day7'],
			'day30'			=> $core->lang['anal_day30'],
			'all'			=> $a ? $core->lang['anal_short'] : $core->lang['anal_full'],

			'u_all'			=> $core->url('m', 'analytics?from=').date2form( $from ).'&to='.date2form( $to ).($o?'&o='.$o:'').($c?'&c='.$c:'').($a?'':'&a=1'),
			'u_today'		=> $core->url('m', 'analytics?from=').date2form( $today ).'&to='.date2form( $today ).($o?'&o='.$o:'').($c?'&c='.$c:'').($a?'&a=1':''),
			'u_yest'		=> $core->url('m', 'analytics?from=').date2form( $yest ).'&to='.date2form( $yest ).($o?'&o='.$o:'').($c?'&c='.$c:'').($a?'&a=1':''),
			'u_day7'		=> $core->url('m', 'analytics?from=').date2form( $day7 ).'&to='.date2form( $today ).($o?'&o='.$o:'').($c?'&c='.$c:'').($a?'&a=1':''),
			'u_day30'		=> $core->url('m', 'analytics?from=').date2form( $day30 ).'&to='.date2form( $today ).($o?'&o='.$o:'').($c?'&c='.$c:'').($a?'&a=1':''),

			'count'			=> $core->lang['anal_count'],
			'income'		=> $core->lang['anal_income'],
			'outcome'		=> $core->lang['anal_outcome'],
			'total'			=> $core->lang['anal_total'],

		));

	    foreach ( $core->lang['statuso'] as $i => $v ) $core->tpl->vars( 'body', array( 'st'.$i => $v ) );
	    foreach ( $core->lang['reasono'] as $i => $v ) $core->tpl->vars( 'body', array( 'rs'.$i => $v ) );
	    foreach ( $core->lang['reasonm'] as $i => $v ) $core->tpl->vars( 'body', array( 'rm'.$i => $v ) );

		foreach ( $offers as $of => $n ) {
			$core->tpl->block( 'body', 'offer', array( 'name' => $n, 'value' => $of, 'select' => ($of==$o) ? 'selected="selected"' : '' ) );
		}

		foreach ( $comps as $cm => $n ) {
			$core->tpl->block( 'body', 'comp', array( 'name' => $n, 'value' => $cm, 'select' => ($cm==$c) ? 'selected="selected"' : '' ) );
		}

		foreach ( $comp as $i => &$z ) {
			$z['name'] = $comps[$i];
			$z['vip'] = $core->wmsale->get( 'comp', $i, 'comp_vip' ) ? $core->lang['iamvip'] : '';
			if ( $c ) foreach ( $cs[$i] as $f ) $man[$f]['name'] = '&mdash; ' . $core->user->get( $f, 'user_name' );
		} unset( $z );

		function nameusort( $aaaa, $bbbb ) { return strcmp( $aaaa['name'], $bbbb['name'] ); }
		foreach ( $user as $i => &$z ) {
			$z['name'] = $i ? $core->user->get( $i, 'user_name' ) : $core->lang['anal_search'];
			$z['vip'] = $i ? ( $core->user->get( $i, 'user_vip' ) ? $core->lang['iamvip'] : '' ) : '';
			$z['ext'] = $i ? ( $core->user->get( $i, 'user_ext' ) ? $core->lang['iamext'] : '' ) : '';
			if ( $ext[$i] ) {				foreach ( $ext[$i] as $n => &$ff ) $ff['name'] = '&mdash; ' . $n;
				ksort( $ext[$i] );
			} elseif ( $a && $uf[$i] ) foreach ( $uf[$i] as $f )  $flow[$f]['name'] = '&mdash; ' . $core->wmsale->get( 'flow', $f, 'flow_name' );
		} unset( $z );

		$sites = $core->wmsale->get( 'lands' );
		foreach ( $offer as $i => &$z ) {
			$z['name'] = $offers[$i];
			if ( ($a || $o) && $os[$i] ) foreach ( $os[$i] as $f ) $site[$f]['name'] = '&mdash; '.$sites[$f];
		} unset( $z );

		uasort( $comp,  'nameusort' );
		uasort( $user,  'nameusort' );
		uasort( $offer, 'nameusort' );

		$core->tpl->block( 'body', 'bl' );
		$total['name'] = $core->lang['total'];
		$core->tpl->block( 'body', 'bl.row', analytics_line($total) );

		$core->tpl->block( 'body', 'bl' );
		$core->tpl->block( 'body', 'bl.t', array( 'name' => $core->lang['anal_comps'] ) );
		foreach ( $comp as $i => $z ) {
			$core->tpl->block( 'body', 'bl.row', analytics_line($z) );
			if ( $c ) foreach ( $cs[$i] as $f ) $core->tpl->block( 'body', 'bl.row', analytics_line($man[$f]) );
		}

		$core->tpl->block( 'body', 'bl' );
		$core->tpl->block( 'body', 'bl.t', array( 'name' => $core->lang['anal_users'] ) );
		foreach ( $user as $i => $z ) {
			$core->tpl->block( 'body', 'bl.row', analytics_line($z) );
			if ( $a && $uf[$i] ) foreach ( $uf[$i] as $f ) $core->tpl->block( 'body', 'bl.row', analytics_line($flow[$f]) );
			if ( $a && $ext[$i] ) foreach ( $ext[$i] as $f ) $core->tpl->block( 'body', 'bl.row', analytics_line($f) );
		}

		$core->tpl->block( 'body', 'bl' );
		$core->tpl->block( 'body', 'bl.t', array( 'name' => $core->lang['anal_offer'] ) );
		foreach ( $offer as $i => $z ) {
			$core->tpl->block( 'body', 'bl.row', analytics_line($z) );
			if ( $a || $o ) foreach ( $os[$i] as $f ) $core->tpl->block( 'body', 'bl.row', analytics_line($site[$f]) );
		}

		$core->tpl->output( 'body' );

		$core->footer ();
		$core->_die();

	  case 'dynamics':

		$today	= date( 'Ymd' );
		$day7	= date( 'Ymd', strtotime( '-7 days' ) );
		$day30	= date( 'Ymd', strtotime( '-30 days' ) );
		$day90	= date( 'Ymd', strtotime( '-90 days' ) );

		if ( isset( $core->get['to'] ) && $core->get['to'] ) {
			$to = form2date( $core->get['to'] );
			if ( $to > $today ) $to = $today;
		} else $to = $today;

		if ( isset( $core->get['from'] ) && $core->get['from'] ) {
			$from = form2date( $core->get['from'] );
			if ( $from > $to ) $from = $to;
		} else $from = $day30;

		$ff = strtotime( date2form( $from ) . ' 00:00:00' );
		$tt = strtotime( date2form( $to ) . ' 23:59:59' );

		$stats = array();
		$oq = $core->db->start( "SELECT cash_time, cash_value FROM ".DB_CASH." WHERE cash_type IN ( 2, 3, 6 ) AND cash_time BETWEEN '$ff' AND '$tt'" );
		while ( $q = $core->db->one( $oq ) ) {
			if ( ! $q['cash_value'] ) continue;
			$d = date( 'Ymd', $q['cash_time'] );
			$v = - $q['cash_value'];
			if ( ! $stats[$d] ) $stats[$d] = array( 'i' => 0, 'o' => 0 );
			$stats[$d][ ( $v > 0 ) ? 'i' : 'o' ] += $v;
		} $core->db->stop( $oq );

		krsort( $stats );
		foreach ( $stats as &$s ) $s['t'] = $s['i'] + $s['o']; unset ( $s );
		reset( $stats );

		list ( $d, $s ) = each ( $stats );
		while( 1 ) {
			$d1 = $d; $s1 = $s;
			list ( $d, $s ) = each ( $stats );
			if ( ! $d ) break;
			$stats[$d1]['d'] = $s1['t'] - $s['t'];
		} reset( $stats );

	    $core->mainline->add ( $core->lang['dynamics'], $core->url( 'm', 'dynamics' ) );
		$core->header ();

		$core->tpl->load( 'body', 'dynamics' );

		$core->tpl->vars( 'body', array(

			'date'			=> $core->lang['date'],
			'income'		=> $core->lang['anal_income'],
			'outcome'		=> $core->lang['anal_outcome'],
			'total'			=> $core->lang['anal_total'],
			'from'			=> date2form( $from ),
			'to'			=> date2form( $to ),
			'show'			=> $core->lang['show'],

			'u_analytics'	=> $core->url( 'm', 'analytics' ),
			'analytics'		=> $core->lang['menu_analytics'],

			'day7'			=> $core->lang['anal_day7'],
			'day30'			=> $core->lang['anal_day30'],
			'day90'			=> $core->lang['anal_day90'],
			'u_day7'		=> $core->url('m', 'dynamics?from=').date2form( $day7 ).'&to='.date2form( $today ),
			'u_day30'		=> $core->url('m', 'dynamics?from=').date2form( $day30 ).'&to='.date2form( $today ),
			'u_day90'		=> $core->url('m', 'dynamics?from=').date2form( $day90 ).'&to='.date2form( $today ),

		));

		foreach ( $stats as $d => $s ) {
			$core->tpl->block( 'body', 'date', array(
				'day'		=> date2form ( $d ),
				'wd'		=> $core->lang['weekday'][date( 'w', strtotime(date2form ( $d )) )],
				'in'		=> rur( $s['i'] ),
				'out'		=> rur( $s['o'] ),
				'total'		=> rur( $s['t'] ),
				'delta'		=> rur( $s['d'] ),
			));
		}

		ksort ( $stats );
		foreach ( $stats as $d => $s ) {
			$core->tpl->block( 'body', 'gr', array(
				'smd'		=> substr( $d, 6, 2 ) . '.' . substr( $d, 4, 2 ),
				'smt'		=> abs( $s['t'] ),
			));
		}

		$core->tpl->output( 'body' );

		$core->footer ();
		$core->_die();

	}

	return false;

}