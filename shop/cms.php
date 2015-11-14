<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			landing zone / cms.php
 *  Description:	Landing site simple CMS
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

header( 'Content-type: text/html; charset=UTF-8' );
$now = time();

// Showing the message
if ( $_SERVER['QUERY_STRING'] == 'done' ) {
?><html>
<head>
	<title>Ваш заказ принят!</title>
	<meta charset="utf-8" />
	<style type="text/css">
		body, h1, h2, p, div { font: normal 12px OpenSans, Segoe UI, Tahoma, sans-serif; }
		body { padding: 40px 10px; text-align: center;  }
		h1 { font-size: 34px; padding: 0; margin: 0 0 20px 0; color: #292; }
		h2 { font-size: 20px; padding: 0; margin: 0 0 20px 0; color: #111; }
		p { font-size: 11px; padding: 0; margin: 0; color: #777; }
		div { font-size: 16px; padding: 2px; margin: 0; color: #822; }
	</style>
</head>
<body>
	<h1>Ваш заказ принят! Спасибо!</h1>
	<h2>Менеджер перезвонит Вам для уточнения деталей в течение часа</h2>
</body>
</html><?php
	die();
} elseif ( $_SERVER['QUERY_STRING'] == 'privacypolicy' ) {?><!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Политика конфиденциальности</title>
	<style>
		body, html{
			min-height: 100%;
			margin:0px;
			padding: 0px;
			background: #eee;
		}
		body{
			padding-top: 40px;
		}
		.block_more_info{
			width: 800px;
			margin: 0px auto 20px;
			background: #fff;
			font-family: Arial;
			padding: 20px 40px 40px 40px;
			border: 1px solid #DADADA;
			line-height: 20px;
		}
		.block_more_info h1{
			color: #3B6A7C;
			margin-bottom: 30px;
			text-align: center;
		}
		.s1{
			font-style: italic;
			text-align: center;
			margin: 40px 0 0 0;
			font-weight: bold;
		}
		h2{
			font-size: 16px;
			margin-top: 26px;
		}
	</style>
</head>
<body>
	<div class="block_more_info">
		<h1>Политика конфиденциальности</h1>

		<h2>Защита личных данных</h2>
		<p>Для защиты ваших личных данных у нас внедрен ряд средств защиты, которые действуют при введении, передаче или работе с вашими личными данными.</p>

		<h2>Разглашение личных сведений и передача этих сведений третьим лицам</h2>
		<p>Ваши личные сведения могут быть разглашены нами  только в том случае это необходимо для: (а) обеспечения соответствия предписаниям закона или требованиям судебного процесса в нашем отношении ; (б) защиты наших прав или собственности (в) принятия срочных мер по обеспечению личной безопасности наших сотрудников или потребителей предоставляемых им услуг, а также обеспечению общественной безопасности. Личные сведения, полученные в наше распоряжение при регистрации, могут передаваться третьим организациям и лицам, состоящим с нами в партнерских отношениях для улучшения качества оказываемых услуг.  Эти сведения не будут использоваться в каких-либо иных целях, кроме перечисленных выше.   Адрес электронной почты, предоставленный вами при регистрации может использоваться для отправки вам сообщений или уведомлений об изменениях, связанных с вашей заявкой, а также  рассылки сообщений о происходящих в компании событиях и изменениях, важной информации о новых товарах и услугах и т.д.  Предусмотрена возможность отказа от подписки на эти почтовые сообщения.</p>

		<h2>Использование файлов «cookie»</h2>
		<p>Когда пользователь посещает веб-узел, на его компьютер записывается файл «cookie» (если пользователь разрешает прием таких файлов). Если же пользователь уже посещал данный веб-узел, файл «cookie» считывается с компьютера. Одно из направлений использования файлов «cookie» связано с тем, что с их помощью облегчается сбор статистики посещения. Эти сведения помогают определять, какая информация, отправляемая заказчикам, может представлять для них наибольший интерес. Сбор этих данных осуществляется в обобщенном виде и никогда не соотносится с личными сведениями пользователей.</p>
		<p>Третьи стороны, включая компании Google, показывают объявления нашей компании на страницах сайтов в Интернете. Третьи стороны, включая компанию  Google, используют cookie, чтобы показывать объявления, основанные на предыдущих посещениях пользователем наших вебсайтов и интересах в веб-браузерах. Пользователи могут запретить компаниям Google использовать cookie. Для этого необходимо посетить специальную страницу компании Google по этому адресу: http://www.google.com/privacy/ads/</p>

		<h2>Изменения в заявлении о соблюдении конфиденциальности</h2>
		<p>Заявление о соблюдении конфиденциальности предполагается периодически обновлять. При этом будет изменяться дата предыдущего обновления, указанная в начале документа. Сообщения об изменениях в данном заявлении будут размещаться на видном месте наших веб-узлов</p>
		<p class="s1">Благодарим Вас за проявленный интерес к нашей системе! </p>
	</div>
</body>
</html><?php
	die();
}

// Getting new flow ID
if ( $_GET['flow'] && $f = (int) $_GET['flow'] ) {
	$newflow = $f;
} elseif ( preg_match( "#^([0-9]+)#i", $_SERVER['QUERY_STRING'], $mf ) ) {
	$newflow = (int) $mf[0];
} else $newflow = false;

// Processing current flow ID
if ( $newflow ) {
	$flow = $newflow;
	setcookie( 'flow', $newflow, $now + 2592000, '/' );
} else $flow = (int) $_COOKIE['flow'];
$unique = ( $newflow && $newflow != $_COOKIE['flow'] ) ? '&u=1' : '';

// Processing external ID
/*if ( $_GET['astt'] && is_numeric( $_GET['astt'] ) ) {
	$exti = 1;
	$extu = preg_replace( '#[^0-9]+#i', '', $_GET['astt'] );
	$exts = $_GET['astsid'] ? preg_replace( '#[^0-9]+#i', '', $_GET['astsid'] ) : 0;
} elseif ( $_GET['l24_uid'] ) {
	$exti = 2;
	$extu = preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_GET['l24_uid'] );
	$exts = (int) $_GET['l24_bid'];
} elseif ( $_GET['affterra'] ) {
	$exti = 8;
	$extu = preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_GET['affterra'] );
	$exts = (int) $_GET['webmaster_id'];
} elseif ( $_GET['adpro'] && is_numeric( $_GET['adpro'] ) ) {
	$exti = 3;
	$extu = preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_GET['prx'] );
	$exts = (int) $_GET['adpro'];
} else */ $exti = $extu = $exts = 0;

// Set up EXT cookie
if ( $exti ) {
	setcookie( 'extd', "$exti:$extu:$exts", $now + 86400, '/' );
} elseif ( $exti = (int) $_GET['exti'] ) {
	$extu = preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_GET['extu'] );
	$exts = preg_replace( '#[^0-9]+#i', '', $_GET['exts'] );
	setcookie( 'extd', "$exti:$extu:$exts", $now + 86400, '/' );
} else list( $exti, $extu, $exts ) = $_COOKIE['extd'] ? explode( ':', $_COOKIE['extd'] ) : array( 0, 0, 0 );

// Processing Space Source
if ( $_GET['sp'] && $from = (int) $_GET['sp'] ) {
	setcookie( 'fromspace', $from, $now + 300, '/' );
} else $from = $_COOKIE['fromspace'] ? (int) $_COOKIE['fromspace'] : false;

// Processing Target
if ( $_GET['t'] && $target = (int) $_GET['t'] ) {
	setcookie( 'targetid', $target, $now + 300, '/' );
} else $target = $_COOKIE['targetid'] ? (int) $_COOKIE['targetid'] : false;

// Minimal UTM analysis
$utmsrc = strtolower( $_GET['utm_source'] );
if ( $_GET['subid'] || $_GET['subcmp'] ) {
	$utmi = 100; $utmc = (int) $_GET['subid']; $utms = (int) $_GET['subcmp'];
} elseif ( $utmsrc == 'marketgid' || $_GET['mgd_src'] ) {
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
if ( $utmi ) {
	setcookie( 'utmd', "$utmi:$utms:$utmc", $now + 86400, '/' );
} elseif ( $utmi = (int) $_GET['utmi'] ) {	$utms = preg_replace ("#([^a-z0-9\-\_\.]*)#si", '', strtolower( $_GET['utms'] ));
	$utmc = (int) $_GET['utmc'];
	setcookie( 'utmd', "$utmi:$utms:$utmc", $now + 86400, '/' );
} else list( $utmi, $utms, $utmc ) = $_COOKIE['utmd'] ? explode( ':', $_COOKIE['utmd'] ) : array( 0, 0, 0 );

// New Flow vs. ExtID
if ( $newflow && $exti ) {
	unset( $exti, $extd, $extu );
	setcookie( 'extd', '', $now - 2592000, '/' );
} elseif ( $exti && $flow ) {
	unset( $flow );
	setcookie( 'flow', '', $now - 2592000, '/' );
}

// Checking for the post requests
$error = $request = false;
if ( $_POST['task'] == 'process' ) {

	// Check the IP
	$ip = filter_var( $_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
	if ( ! $ip ) $ip = filter_var( $_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
	if ( ! $ip ) $ip = $_SERVER['REMOTE_ADDR'];

	// Creating post request array
	$request = array(
		'offer'		=> OFFER,
		'site'		=> SITE,
		'flow'		=> (int) $_POST['flow'],
		'from'		=> (int) $_POST['from'],
		'target'	=> (int) $_POST['target'],
		'utmi'		=> (int) $_POST['utmi'],
		'utms'		=> preg_replace ("#([^a-z0-9\-\_\.]*)#si", '', strtolower( $_POST['utms'] )),
		'utmc'		=> (int) $_POST['utmc'],
		'exti'		=> (int) $_POST['exti'],
		'extu'		=> preg_replace( '#[^0-9A-Za-z\-\_\.]+#i', '', $_POST['extu'] ),
		'exts'		=> preg_replace( '#[^0-9]+#i', '', $_POST['exts'] ),
		'ip'		=> $ip,
		'name'		=> $_POST['name'],
		'addr'		=> $_POST['address'] ? $_POST['address'] : ( defined('ADDR') ? ADDR : '' ),
		'phone'		=> $_POST['phone'],
		'comm'		=> $_POST['comment'] ? $_POST['comment'] : '',
		'country'	=> $_POST['country'] ? strtolower(substr( $_POST['country'], 0, 2 )) : '',
		'count'		=> defined( 'COUNT' ) ? COUNT : 1,
		'discount'	=> defined( 'DSCNT' ) ? DSCNT : 0,
		'more'		=> defined( 'MORE' ) ? MORE : 0,
	);
	$skey = hash_hmac( 'sha1', http_build_query( $request ), SKEY );

	// Posting to the base site
	$curl = curl_init( BASEURL . 'neworder?key=' . $skey );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_POST, 1 );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, $request );
	$result = curl_exec( $curl );
	curl_close( $curl );

	// Checkign for errors or success
	$r = explode ( ':', $result, 2 );
	if ( $r[0] == 'e' ) {
		switch ( $r[1] ) {
         	case 'data':	$error = 'Ошибка заполнения формы!'; break;
         	case 'key':		$error = 'Ошибка сервера: поставщик неизвестен ...'; break;
         	case 'site':	$error = 'Ошибка сервера: сайт неизвестен ...'; break;
         	case 'offer':	$error = 'Ошибка сервера: товар неизвестен ...'; break;
         	case 'db':		$error = 'Внутренняя ошибка сервера ...'; break;
         	case 'ban':		$error = 'Вы занесены в чёрный список!';	break;
         	case 'security':$error = 'Заказ отклонён службой безопасности системы!';	break;
         	default:		$error = 'Произошла неизвестная ошибка сервера ...';
		}
	} else {
		if ( $r[0] != 'ok' ) file_put_contents( PATH.'query.txt', serialize(array( $skey, $request )) . "\r\n", FILE_APPEND | LOCK_EX  );
		header( 'Location: '.SHOPURL.'?done' );
		die();
	}

} elseif ( $flow || $target ) {	$req = 's=' . SITE . '&f=' . $flow . $unique . ( $target ? '&t='.$target : '' ) . ( $utmi ? '&utmi=' . $utmi : '' ) . ( $utms ? '&utms=' . $utms : '' ) . ( $utmc ? '&utmc=' . $utmc : '' );
	$res = @file_get_contents( BASEURL . 'click.php?' . $req );
	if (!( $res == 'ok' || $res == 'e' )) file_put_contents( PATH.'click.txt', $req . "&tm=$now\r\n", FILE_APPEND | LOCK_EX  );
}

$params = '<input type="hidden" name="task" value="process" />'."\n";
if ( $target ) $params .= '<input type="hidden" name="target" value="'.$target.'" />'."\n";
if ( $utmi ) $params .= '<input type="hidden" name="utmi" value="'.$utmi.'" />'."\n";
if ( $utms ) $params .= '<input type="hidden" name="utms" value="'.$utms.'" />'."\n";
if ( $utmc ) $params .= '<input type="hidden" name="utmc" value="'.$utmc.'" />'."\n";
if ( $flow ) $params .= '<input type="hidden" name="flow" value="'.$flow.'" />'."\n";
if ( $from ) $params .= '<input type="hidden" name="from" value="'.$from.'" />'."\n";
if ( $exti ) $params .= '<input type="hidden" name="exti" value="'.$exti.'" />'."\n";
if ( $extu ) $params .= '<input type="hidden" name="extu" value="'.$extu.'" />'."\n";
if ( $exts ) $params .= '<input type="hidden" name="exts" value="'.$exts.'" />'."\n";
if ( $error ) $params .= '<script type="text/javascript">alert("Невозможно выполнить заказ.\\n'.$error.'");</script>';