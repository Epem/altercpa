<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / crypto.php
 *  Description:	Simple crypto library
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

class crypto {

	public function __costruct () { }

	//
	// Function Returns the <Shift Number> for the encode / decode functions
	public static function shift ($crypt_key, $letter_num) {

	    $block_num      = ceil ($letter_num / 32) - 1;
	    $letter_num    -= $block_num * 32;
	    $crypt_key_new  = substr($crypt_key, $block_num, 1000) . substr($crypt_key, 0, $block_num);
	    $crypt_md5      = md5 ($crypt_key_new);
	    $letter         = substr($crypt_md5, $letter_num, 1);
	    $letter_code    = ord($letter);

	    return $letter_code;

	}
	//

	//
	// Fuction encrypts the text with specified crypt key
	public static function encode ($text, $crypt_key) {

	    $out = '';
	    $length = strlen($text);
	    for ($i = 0; $i < $length; $i++) {
	        $letter = substr($text, $i, 1);
	        $code   = ord ($letter);
	        $code   = ($code + crypto::shift ($crypt_key, $i)) % 256;
	        $letter = sprintf('%02x', $code);
	        $out .= $letter;
	    }

	    return $out;

	}
	//

	//
	// Function Decrypts the text with specified crypt key
	public static function decode ($text, $crypt_key) {

	    $out = '';
	    $length = strlen($text) / 2;
	    for ($i = 0; $i < $length; $i++) {
	        $letter = substr($text, $i*2, 2);
	        $code   = (int) hexdec ($letter);
	        $code   = (256 + ($code - crypto::shift ($crypt_key, $i))) % 256;
	        $letter = chr ($code);
	        $out .= $letter;
	    }

	    return $out;

	}
	//

}

?>