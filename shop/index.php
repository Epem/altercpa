<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			landing zone / index.php
 *  Description:	Landing Zone Shop
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

// Preparing offers list
if ( @filemtime( PATH . 'offers.txt' ) < time() - 3600 ) {	$data = file_get_contents( BASEURL . 'api/wm/pub.json' );
	if ( $data ) {		$offers = json_decode( $data, true );
		file_put_contents( PATH . 'offers.txt', $data );
	} else $offers = json_decode( file_get_contents( PATH . 'offers.txt' ), true );
} else $offers = json_decode( file_get_contents( PATH . 'offers.txt' ), true );

// Preparing offer pictures
foreach ( $offers as $i => $o ) if ( @filemtime( PATH . 'pic'.$i.'.jpg' ) < $o['imgt'] )  {	$picdata = file_get_contents( $o['image'] );
	file_put_contents( PATH . 'pic'.$i.'.jpg', $picdata );
	unset ( $picdata );
}

shuffle( $offers );

?><!DOCTYPE html>
<html lang="ru">
<head>
	<title>Интернет-магазин</title>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body>

<div id="container">

<? if (strpos( $_SERVER['QUERY_STRING'], 'done' ) !== false ) : ?>
	<div id="done">
		<h1>Ваш заказ принят! Спасибо!</h1>
		<h2>Менеджер перезвонит Вам для уточнения деталей в течение часа</h2>
		<p>А пока Вы можете ознакомиться с другими предложениями нашей компании:</p>
	</div>
<? endif; ?>

	<div id="offers">
	<? foreach ( $offers as $o ) : ?>
		<div class="the-offer">
			<a class="imga" href="http://<?=$o['url'];?>"><img class="offer-image" src="pic<?=$o['id'];?>.jpg" alt="<?=$o['name'];?>" /></a>
			<div class="offer-name">
				<span class="rur green"><?=$o['price'];?></span>
				<a href="http://<?=$o['url'];?>"><?=$o['name'];?></a>
			</div>
			<div class="offer-descr"><?=$o['descr'];?></div>
			<a class="offer-add" href="http://<?=$o['url'];?>">Подробнее на сайте <span><?=$o['url'];?></span></a>
		</div>
	<? endforeach; ?>	</div>

</div>

</body>
</html>