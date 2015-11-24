<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / search.php
 *  Description:	Database SearchWords processing
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
// Search Words Processing
class SearchWords {
	private $text;				// Text as string
	private $words = array();	// Text as array of words
	public	$strict = false;	// Strict search (no % used)

	// Sets searchword if needed
	public function __construct( $text = false ) {		if ( $text ) $this->set ( $text );
	}

	public function __destruct() { }

	// Returns cleaned up search text
	public function get() {
		return $this->text ? $this->text : false;
	} public function __toString() { return $this->get(); }

	// Sets up search request and returns cleaned up text
	public function set ( $request ) {
		if ( mb_strlen( $request ) < 3 ) return false;

		$this->words = array();
		$reqs = preg_split( '/[\s,]+/i', $request, -1, PREG_SPLIT_NO_EMPTY );
		foreach ( $reqs as &$r ) {
			if ( $r = $this->clean( $r ) ) $this->words[] = mb_strtolower( $r );		} unset( $reqs, $r );

		$this->words = array_unique( $this->words );

		if ( count( $this->words ) ) {			return $this->text = implode( ' ', $this->words );
		} else return false;

	}

	// Highlights text with previosly set request by tag with params
	public function highlight( $text, $tag = 'b', $param = null ) {
    	if ( count( $this->words ) ) {
			$reps = $param ? '<'. $tag .' '. $param .">\\0</". $tag .'>' : '<'. $tag .">\\0</". $tag .'>';
			foreach ( $this->words as &$r ) {
				$text = preg_replace( "|$r|uUi" , $reps, $text );
			} unset( $r );
			return $text;

		} else return $text;

	}

	// Search SQL-query for fields
	public function field( $fields, $ors = false, $both = false ) {
		if ( !is_array( $fields ) ) $fields = array( $fields );

		$query = array();
		foreach ( $fields as $field ) {
			$line = array();
			foreach ( $this->words as &$r ) {
				$line[] = " `$field` LIKE '%$r%' ";
			} unset( $r );
			$query[] = ' ( ' . implode( $ors ? 'OR' : 'AND', $line ) . ' ) ';
		}
		return implode( $both ? 'AND' : 'OR', $query );

	}

	// Search word cleaning up
	public static function clean( $request ) {
		$unstring = '\'"()&|%$#@*!^:;?<>,[]{}\\/'; $unarr = array();
		for( $i = 0; $i < strlen($unstring); $i++ ) $unarr[ $unstring{$i} ] = '';

		$request = mb_substr( trim($request), 0, 50 );
		$request = strtr( $request, $unarr );
		$request = preg_replace( '#\s+#i', ' ', $request );
		$request = trim( $request );

		return ( mb_strlen( $request ) > 2 ) ? $request : '';

	}

	// Russian Keyboard Translation
	public static function rutrans( $text ) {
		$ft = array(
			'q' => 'й', 'w' => 'ц', 'e' => 'у', 'r' => 'к', 't' => 'е', 'y' => 'н', 'u' => 'г', 'i' => 'ш', 'o' => 'щ', 'p' => 'з', '[' => 'х', ']' => 'ъ', 'a' => 'ф', 's' => 'ы', 'd' => 'в', 'f' => 'а', 'g' => 'п', 'h' => 'р', 'j' => 'о', 'k' => 'л', 'l' => 'д', ';' => 'ж', "'" => 'э', 'z' => 'я', 'x' => 'ч', 'c' => 'с', 'v' => 'м', 'b' => 'и', 'n' => 'т', 'm' => 'ь', ',' => 'б', '.' => 'ю',
			'Q' => 'Й', 'W' => 'Ц', 'E' => 'У', 'R' => 'К', 'T' => 'Е', 'Y' => 'Н', 'U' => 'Г', 'I' => 'Ш', 'O' => 'Щ', 'P' => 'З', '{' => 'Х', '}' => 'Ъ', 'A' => 'Ф', 'S' => 'Ы', 'D' => 'В', 'F' => 'А', 'G' => 'П', 'H' => 'Р', 'J' => 'О', 'K' => 'Л', 'L' => 'Д', ':' => 'Ж', '"' => 'Э', 'Z' => 'Я', 'X' => 'Ч', 'C' => 'С', 'V' => 'М', 'B' => 'И', 'N' => 'Т', 'M' => 'Ь', '<' => 'Б', '>' => 'Ю',
		);
		return strtr( $text, $ft );

	}

}
//