<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			spacing zone / cms.php
 *  Description:	Spacing site simple CMS
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

// Loading configuration
define( 'PATH', dirname(__FILE__) . '/' );
require_once PATH . 'config.php';

// URL creator
header( 'Content-type: text/html; charset=utf-8' );
function geturl ( $lands, $space, $defland ) {

	global $flow;
	$land = false;

	// Processing new flow from AlterCPA
	if ( $_GET['flow'] && $f = preg_replace( '#[^0-9\-]#i', '', $_GET['flow'] ) ) {
		$newflow = $f;
	} elseif ( preg_match( "#^([0-9\-]+)#i", $_SERVER['QUERY_STRING'], $mf ) ) {
		$newflow = $mf[0];
	} else $newflow = false;
	$unique = ( $newflow && $newflow != $_COOKIE['flow'] ) ? '&u=1' : '';

	// Setting up flow id
	if ( $newflow ) {
		$flow = $newflow;
		setcookie( 'flow', $newflow, time() + 2592000, '/' );
	} else $flow = $_COOKIE['flow'] ? preg_replace( '#[^0-9\-]#i', '', $_COOKIE['flow'] ) : false;

	// Processing External ID
	/* if ( $_GET['astt'] && is_numeric( $_GET['astt'] ) ) {
		$exti = 1;
		$extu = preg_replace( '#[^0-9]+#i', '', $_GET['astt'] );
		$exts = $_GET['astsid'] ? preg_replace( '#[^0-9]+#i', '', $_GET['astsid'] ) : 0;
	} elseif ( $_GET['l24_uid'] ) {
		$exti = 2;
		$extu = preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_GET['l24_uid'] );
		$exts = (int) $_GET['l24_bid'];
	} elseif ( $_GET['adpro'] && is_numeric( $_GET['adpro'] ) ) {
		$exti = 3;
		$extu = preg_replace( '#[^0-9A-Za-z\-\_\.]+#i', '', $_GET['prx'] );
		$exts = (int) $_GET['adpro'];
	} else */ $exti = $extu = $exts = 0;

	// Set up EXT cookie
	if ( $exti ) {
		setcookie( 'extd', "$exti:$extu:$exts", time() + 86400, '/' );
	} else list( $exti, $extu, $exts ) = $_COOKIE['extd'] ? explode( ':', $_COOKIE['extd'] ) : array( 0, 0, 0 );

	// New Flow vs. ExtID
	if ( $newflow && $exti ) {
		unset( $exti, $extd, $extu );
		setcookie( 'extd', '', time() - 2592000, '/' );
	} elseif ( $exti && $flow ) {
		unset( $flow );
		setcookie( 'flow', '', time() - 2592000, '/' );
	}

	// Choosing langing
	if ( $flow ) {
		if ( strpos( $flow, '-' ) !== false ) {
			$ff = explode( '-', $flow );
			$flow = (int) $ff[0];
			$land = (int) $ff[1];
		}
	}

	// Choose landing
	if ( isset( $_GET['l'] ) ) $land = (int) $_GET['l'];
	if ( ! $lands[$land] ) $land = $defland;

	// Choosing partner program
	if ( $flow ) {
		$line = $flow;
	} elseif ( $exti ) {
		$line = 'exti=' . $exti;
		if ( $extu ) $line .= '&extu=' . $extu;
		if ( $exts ) $line .= '&exts=' . $exts;
	} else $line = '';

	// Processing Target
	if ( $_GET['t'] && $target = (int) $_GET['t'] ) {
		setcookie( 'targetid', $target, time() + 300, '/' );
	} else $target = $_COOKIE['targetid'] ? (int) $_COOKIE['targetid'] : false;
	if ( $target ) $line .= ( $line ? '&' : '?' ) . 't=' . $target;
	//
	// Check for UTM id and source
	//

	// Minimal analysis
	$utmsrc = strtolower( $_GET['utm_source'] );
	if ( $utmsrc == 'marketgid' || $_GET['mgd_src'] ) {
		$utmi = 1; $utmc = (int) $_GET['utm_content'];
		if ( $_GET['mgd_src'] && is_int( $_GET['mgd_src'] ) ) {
			$utms = (int) $_GET['mgd_src'];
		} else $utms = (int) $_GET['utm_term'];
	} elseif ( $utmsrc == 'directadvert.ru' ) {
		$utmi = 2; $utmc = (int) preg_replace( '#([^0-9]+)#', '', $_GET['utm_content'] );
		$utms = (int) preg_replace( '#([^0-9]+)#', '', $_GET['utm_campaign'] );
	} elseif ( $utmsrc == 'advertlink' ) {
		$utmi = 3; $utmc = (int) preg_replace( '#([^0-9]+)#', '', $_GET['utm_content'] );
		$utms = (int) preg_replace( '#([^0-9]+)#', '', $_GET['utm_campaign'] );
	} elseif ( $utmsrc == 'targetmailru' ) {
		$utmi = 5; $utmc = (int) preg_replace( '#([^0-9]+)#', '', $_GET['utm_content'] );
		$utms = (int) preg_replace( '#([^0-9]+)#', '', $_GET['utm_campaign'] );
	} elseif ( strtolower( $_GET['utm_campaign'] ) == 'redtram' ) {
		$utmi = 4; $utmc = (int) preg_replace( '#([^0-9]+)#', '', $_GET['utm_content'] ); $utms = 0;
	} else $utmi = $utms = $utmc = 0;

	// Set up UTM cookie
	if ( $utmi ) {		setcookie( 'utmd', "$utmi:$utms:$utmc", time() + 86400, '/' );
	} else list( $utmi, $utms, $utmc ) = $_COOKIE['utmd'] ? explode( ':', $_COOKIE['utmd'] ) : array( 0, 0, 0 );	if ( $utmi ) $line .= '&utmi=' . $utmi . ( $utms ? '&utms=' . $utms : '' ) . ( $utmc ? '&utmc=' . $utmc : '' );

	// Checking for the Spacer ID
	$ru = explode( '/', trim( strtr( $_SERVER['REQUEST_URI'], '?', '/' ), '/' ) );
	$uu = str_replace( 'www.', '', $_SERVER['HTTP_HOST'] ) . '/' . $ru[0];
	if ( $sp = $space[$uu] ) {
		$line .= '&sp=' . $sp;
		if ( $flow ) {			$req = 's=' . $sp . '&f=' . $flow . $unique . ( $target ? '&t='.$target : '' ) . '&p=1' . ( $utmi ? '&utmi=' . $utmi : '' ) . ( $utms ? '&utms=' . $utms : '' ) . ( $utmc ? '&utmc=' . $utmc : '' );
			$res = @file_get_contents( BASEURL . 'click.php?' . $req );
			if (!( $res == 'ok' || $res == 'e' )) file_put_contents( PATH.'click.txt', $req . "\r\n", FILE_APPEND | LOCK_EX  );
		}
	}

	$theurl = $lands[$land] . $line;
	return $theurl;

}

function footer () {

if ( strpos( $_SERVER['QUERY_STRING'], 'cb' ) === false ) return false;

?><script type="text/javascript">
		var ua = navigator.userAgent;
    	if (!ua.match(/MSIE/)){
			var d = document.getElementsByTagName('a'), i = d.length, j;
			while(i--){
				j = d[i].attributes.length;
				while(j--){
					if(d[i].attributes[j].name == 'target'){
						d[i].removeAttribute(d[i].attributes[j].name);
					}
				}
			}


			var comebacker = null;
			var cb_jqi = false;
			var cb_ale = false;

			function cb_iJQ() {
				if (!window.jQuery) {
					if (!cb_jqi) {
						if (typeof $ == 'function') {
							cb_ale = true;
						}
						var script = document.createElement('script');
						script.type = "text/javascript";
						script.src = "//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js";
						document.getElementsByTagName('head')[0].appendChild(script);
						cb_jqi = true;
					}
					setTimeout('cb_iJQ()', 50);
				} else {
					if (true == cb_ale) {
						$j = jQuery.noConflict();
					} else {
						$j = jQuery;
					}

					comebacker = '{"settings":{"script_path":"","page_to":"<?=addslashes(ourl());?>","how_often_show":"every_time","button_name_capitalization":"first_upper","work_page":"","working_in_opera":"on","working_in_opera_after":"3"},"exit_text":"**********************************\\n\\nВНИМАНИЕ!!! УНИКАЛЬНЫЙ ШАНС! СКИДКА 50%!\\n\\n**********************************\\n\\nТолько СЕЙЧАС в течение 30 минут у Вас есть шанс получить скидку 50%!\\n\\nНажмите на кнопку \\"Остаться на странице\\" и получите ГРАНДИОЗНУЮ СКИДКУ!","bar":{"link_text_left":"\u0421\u0434\u0435\u043b\u0430\u043d\u043e \u0441 \u043f\u043e\u043c\u043e\u0449\u044c\u044e \\\"Comebacker\\\"","link_text_right":"\u0414\u0430\u043d\u043d\u044b\u0439 \u0441\u043a\u0440\u0438\u043f\u0442 \u043c\u043e\u0436\u043d\u043e \u043f\u043e\u043b\u0443\u0447\u0438\u0442\u044c \u043a\u043b\u0438\u043a\u043d\u0443\u0432 \u0441\u044e\u0434\u0430","link_href":"<?=addslashes(ourl());?>","height":"0","background_color":"c9c7c7","link_size":"0","link_color":"242424"},"module_where_loaded":"site"}';
					jQuery.ajax({
						type: 'GET',
						url: '/comebacker/comebacker.js',
						data: {},
						dataType: "script"
					});
				}
			}
			cb_iJQ();
		}
	</script><?

}
