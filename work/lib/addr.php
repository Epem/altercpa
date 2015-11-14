<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / addr.php
 *  Description:	Address parser
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

function normalizeaddr ( $addr ) {
	$addr = str_replace( '.', '. ', $addr );
	$addr = str_replace( ',', ', ', $addr );
	$addr = preg_replace( '/([0-9]+)/', ' $1 ', $addr );
	$addr = preg_replace( '/\.+/', '.', $addr );
	$addr = preg_replace( '/\,+/', ',', $addr );
	$addr = preg_replace( '/ +/', ' ', $addr );
	$addr = preg_replace( '/([0-9]+) ([a-zа-я]{1})[ \.\,]/', '$1$2', $addr );
	$addr = str_replace( '. ,', '.,', $addr );
	return $addr;

}

function parseaddress ( $addr ) {
	$addr = iconv( 'utf-8', 'windows-1251', $addr );
	$cached = sprintf( ADDR_CACHE, md5( $addr ) );
	if (  !file_exists( $cached ) ) {

		if (defined( 'ADDR_XML' )) {

			$data = array();
			$info = file_get_contents( ADDR_XML . rawurlencode( $addr ) );

			if ( $info ) {
				$dp = simplexml_load_string( $info );
				foreach ( $dp->Address->Field as $f ) {
					$ff = $f->attributes();
					$value = (string) $ff['name'];
					if ( $value ) $data[] = array( 'id' => strtolower( $ff['level'] ), 'value' => $value, 'type' => (string) $ff['type']  );
				}

			}

		}

		if ( ! $data ) {

			if (defined( 'ADDR_ALT' )) {

				$data = array();
				$info = file_get_contents( ADDR_ALT . rawurlencode( $addr ) );

				if ( $info ) {

					$dp = simplexml_load_string( $info );
					foreach ( $dp->Address->Field as $f ) {
						$ff = $f->attributes();
						$value = (string) $ff['name'];
						if ( $value ) $data[] = array( 'id' => strtolower( $ff['level'] ), 'value' => $value, 'type' => (string) $ff['type']  );
					}

				}

			} else {

				$curl = curl_init( 'http://ahunter.ru/site/search' );
				curl_setopt( $curl, CURLOPT_REFERER, 'http://ahunter.ru/site/search' );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt( $curl, CURLOPT_POST, 1 );
				curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0' );
				curl_setopt( $curl, CURLOPT_POSTFIELDS, 'text_to_process='.rawurlencode( $addr ) );
				curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
					'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3',
					'Connection: keep-alive',
					'Content-Type: application/x-www-form-urlencoded',
					'Accept-Encoding: gzip, deflate'
				));
				$result = curl_exec( $curl );
				curl_close( $curl );

				$dom = new DOMDocument();
			   	$dom->loadHTML( $result );

				if ( !function_exists( 'domelementbyclass' ) ) {					function domelementbyclass( $dom, $tag, $class ) {
						$els = $dom->getElementsByTagName( $tag );
						foreach ( $els as $e ) {
							if ( $e->getAttribute( 'class' ) == $class ) return $e;
						} return false;
					}
				}

				$data = array();
				$table = domelementbyclass ( $dom, 'table', 'address' );
				if ( $table ) {

					$trs = $table->getElementsByTagName( 'tr' );

					foreach ( $trs as $tr ) {
						$row = array();
						$tds = $tr->getElementsByTagName( 'td' );
						foreach ( $tds as $td ) {
							if ( $td->getAttribute( 'class' ) == 'type' ) $row['type'] = $td->nodeValue;
							if ( $td->getAttribute( 'class' ) == 'name' ) {
								$row['value'] = $td->nodeValue;
								$row['id'] = strtolower( $td->getAttribute( 'title' ) );
							}
						}
						$data[] = $row;
					}

				} else $data = array();

			}

		}

		file_put_contents( $cached, serialize( $data ) );
		return $data;

	} else return unserialize(file_get_contents( $cached ));

}