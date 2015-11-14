<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			addr.php
 *  Description:	Address parsing interface
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

// Loading Site Core
error_reporting ( 0 );
define ( 'PATH', dirname(__FILE__).'/' );
header ( 'Content-Type: text/html; charset=utf-8' );
require_once PATH . 'core/settings.php';
require_once PATH . 'lib/addr.php';

$addr = normalizeaddr(stripslashes( $_POST['addr'] ));
$cached = PATH . 'data/cache/addr-' . md5( $addr ) . '.txt';

if ( ! file_exists( $cached ) ) {

	$data = parseaddress( $addr );

	if ( count( $data ) ) {

		$ind = 0;
		$ad = '';
		$region = $city = $street = $house = '';
		foreach ( $data as $d ) {
			if ( $d['id'] == 'zip' ) {				$ind = $d['value'];
				continue;
			} else {				if ( $d['id'] == 'region' || $d['id'] == 'district' ) {					if ( $d['type'] == 'Респ' || $d['type'] == 'г' ) {						$ad .= trim( $d['type'], '. ' ) . '. ' . $d['value'] . ', ';
					} else $ad .= $d['value'] . ' ' . $d['type'] . ', ';
				} else {					if ( $d['type'] == 'ДОМ' || $d['type'] == 'дом' ) $d['type'] = 'д';
					$ad .= trim( $d['type'], '. ' ) . '. ' . $d['value'] . ', ';
				}
			}

        	if ( $d['id'] == 'street' ) $street = trim( $d['type'], '. ' ) .'. '.$d['value'];
        	elseif ( $d['id'] == 'city' || $d['id'] == 'place' ) $city .= $d['value'] . ', ';
        	elseif ( $d['id'] == 'district' ) $city .=  $d['value'] . ' ' .trim( $d['type'], '. ' ).', ';
			elseif ( $d['id'] == 'region' ) {
			    if ( $d['type'] != 'г' ) {
					if ( $d['type'] == 'Респ' ) {
						$area = trim( $d['type'], '. ' ) . '. ' . $d['value'];
					} else $area = $d['value'] . ' ' . $d['type'];
				} else $city = $d['value'] . ', ';
			} else {
				if ( $d['type'] == 'ДОМ' || $d['type'] == 'дом' ) $d['type'] = 'д';
				$house .= trim( $d['type'], '. ' ) . '. ' . $d['value'] . ', ';
			}

		}

		$ad = trim( $ad, ' ,' );
		$house = trim ( $house, ', ' );
		$city = trim ( $city, ', ' );

		if ( $city == 'Москва' && ! $area ) $area = 'Московская обл.';
		if ( $city == 'Санкт-Петербург' && ! $area ) $area = 'Ленинградская обл.';

		$result = array(
			'status'	=> 'ok',
			'ind'		=> $ind,
			'addr'		=> $ad,
			'area'		=> $area,
			'city'		=> $city,
			'street'	=> $street,
			'house'		=> $house,
			'text'		=> sprintf( "Адрес распознан.\nИндекс: %s\nАдрес: %s\nОбласть: %s\nГород: %s\nУлица: %s\nДом: %s\nПодставить её в заказ?", $ind, $ad, $area, $city, $street, $house ),
		);

	} else $result = array( 'status' => 'error', 'text' => sprintf( 'Не удалось распознать адрес: %s', $addr ) );

	$result = json_encode( $result );
	file_put_contents( $cached, $result );
	echo $result;

} else readfile( $cached );

// end. =)