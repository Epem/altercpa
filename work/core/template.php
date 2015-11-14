<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / template.php
 *  Description:	Template Processing
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

class Template {

	// Array of template data
	private $_tpldata = array();

	// Hash of filenames for each template handle.
	private $files = array();

	// Template Path Formats
	private $basic 		= '';
    private	$modular 	= '';
    private	$cache		= '';

	// Code Memory
	private $code = array();

	// Constructor - sets root formats and
	public function __construct ($configs = array()) {

        if (isset($configs['basic']))		$this->basic 	= $configs['basic'];
        if (isset($configs['modular']))		$this->modular 	= $configs['modular'];
        if (isset($configs['cache']))		$this->cache 	= $configs['cache'];

        return true;

	}

	// Destructor - saving cached data
	public function __destruct () { }

	// Prepares File for Template Parsing
    public function load ( $part, $name, $module = null ) {

		$path = $module ? @sprintf( $this->modular, $module, $name ) : @sprintf( $this->basic, $name );
		if ( $path && file_exists( $path ) ) {
			$this->files[$part] = $path;
		} else die ("Error loading template from $path");

    }

    // Setting Root Level Variables for Part
    public function vars ( $part, $v ) {
		if (!isset( $this->_tpldata[$part] )) $this->_tpldata[$part] = array();
		if (is_array( $v )) $this->_tpldata[$part] = array_merge( $this->_tpldata[$part], $v );
    }

    // Setting Block Level Variables for Part
    public function block ( $part, $block, $v = array() ) {

		$ptr = &$this->_tpldata[$part];
		$blocks = explode( '.', $block );

		foreach ( $blocks as $i => $b ) {
			if ( $i ) $ptr = &$ptr[count($ptr)-1];
			$b = $b.'.';
			if (!isset( $prt[$b] )) $prt[$b] = array();
			$ptr = &$ptr[$b];
		}

		$ptr[] = $v;

    }

	// Parses the Part of Output
	public function output ( $part, $charset = false ) {

		// Checking template for existance
    	if (empty( $this->files[$part] )) die ("Template error - no '$part' loaded");
		$path = sprintf( $this->cache, md5( $this->files[$part] ) );

		// Compiling template
		$fmt1 = filemtime( $this->files[$part] );
		$fmt2 = (file_exists( $path )) ? filemtime( $path ) : 0;
		if ( $fmt1 > $fmt2 ) {
			$code = file_get_contents( $this->files[$part] );
			$code = preg_replace( '#\{([0-9a-z\-_]+)\}#i', '<?=$_data[\'$1\'];?>', $code );
			$code = preg_replace( '#\{(([0-9a-z\-_]+)\.)*([0-9a-z\-_]+)\.([0-9a-z\-_]+)\}#i', '<?=$$3[\'$4\'];?>', $code );
			$code = preg_replace( '#<!-- BEGIN ([0-9a-z\-_]+) -->#i', '<? array_push ( $_stack, $_node ); unset ( $_o ); $_o = &$$_node; $_node = \'$1\'; if (is_array( $_o[\'$1.\'] )) foreach ( $_o[\'$1.\'] as &$$1 ) : ?>', $code );
			$code = preg_replace( '#<!-- END ([0-9a-z\-_]+) -->#i', '<? endforeach; unset ( $$1 ); $_node = array_pop ( $_stack ); ?>', $code );
			file_put_contents( $path, $code );
			unset ( $code );
		}

		// Showing the template
       	$_data = &$this->_tpldata[$part];
       	$_node = '_data';
       	$_stack = array();

		if ( $charset ) {
			ob_start();
			include $path;
			$qq = ob_get_contents();
			ob_end_clean();
			error_reporting( E_ALL );
			echo iconv( 'UTF-8', $charset, $qq );
		} else include $path;

	}

}

// end. =)