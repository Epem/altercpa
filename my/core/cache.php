<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			cache.php
 *  Description:	Caching engine
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
// Main Cache Controller
class CacheControl {

	private $__modules = array ();
    private $__path;
    private $__type = 0;

	// MemCache Data
    private $__mc;
    private $__mco;

    // Setting the Path
    public function __construct ( $path, $mc = null ) {

		$this->__path = $path;
		$this->__mc = $mc;

    }

    // Clearing Inner Modules
    public function __destruct () {
    	$this->__end();
    }

    public function __start() {
		if ( $this->__mc ) {
			if ( class_exists( 'Memcache' ) ) {				$this->__mco = new Memcache;
				$this->__type = $this->__mco->connect( $this->__mc['host'], $this->__mc['port'] ) ? 2 : 1;
			} else $this->__type = 1;
		} else $this->__type = 1;

    }

    public function __end() {
    	foreach ($this->__modules as &$m) {
			$m->__close();
        }

        if ( $this->__type == 2 ) {        	$this->__mco->close();
        }

    }

    // Return Inner Module
    public function __get ($name) {

		if ( ! $this->__type ) {        	$this->__start();
		}

    	// Create Module if Empty
    	if (empty( $this->__modules[$name] )) {
			$this->__modules[$name] =
				$this->__type == 2 ?
				new MemCacheModule( $this->__mc['pref'], $name, $this->__mco, $this->__mc['exp'] ) :
				new CacheModule ( $this->__path, $name );
        }

        return $this->__modules[$name];

    }

    public function __mcx() {
		if ( ! $this->__type ) {
        	$this->__start();
		}

		return $this->__type == 2 ? $this->__mco : null;

    }

    public function __mcn( $engine, $name = null ) {    	return $this->__mc['pref'] . md5( $engine . '_' . $name );
    }

}
//

//
// Cache Inner Module
class CacheModule {

	private $__name;
    private $__path;
    private $__open = true;
	private $__data = array ();
    private $__mods = array ();

    // Setting Up Variables
    public function __construct ($path, $name) {

		$this->__path = $path;
        $this->__name = $name;
        $this->__open = true;
        $this->__data = array ();
        $this->__mods = array ();


    }

    // Saving the Contents
    public function __destruct () {

		$this->__close();

    }

    public function __close() {
		if ( $this->__open ) {

			$this->__open = false;
	    	foreach ( $this->__mods as $m => $t ) {
	         	$n = $this->cachename ( $m );
	            $d = serialize ( $this->__data[$m] );
	            file_put_contents ( sprintf($this->__path, $n), $d );
	        }

		}

    }

    // Getting Cached Variable
    public function __get ($name) {

    	if (!isset($this->__data[$name])) {
			$this->load ($name);
        }

        return $this->__data[$name];

    }

    public function __isset ( $name ) {
    	if (!isset($this->__data[$name])) {
			$this->load ($name);
        }

        return isset($this->__data[$name]);

    }

    // Setting Cache Variable Data
    public function __set ($name, $value) {

    	if (!isset($this->__data[$name])) {
			$this->load ($name);
        }

		$this->__data[$name] = $value;
        $this->__mods[$name] = true;

    }

    // Loading Data Block
    private function load ($name) {

        $n = $this->cachename ($name);
		if (file_exists(sprintf($this->__path, $n))) {
        	$d = file_get_contents (sprintf($this->__path, $n));
	        $this->__data[$name] = unserialize ($d);
        } else $this->__data[$name] = null;

    }

    // Delete Cache Data
	public function clear ($name) {
        $n = $this->cachename ( $name );
		$this->__data[$name] = null;
        unset( $this->__mods[$name] );
        @unlink (sprintf($this->__path, $n));
    }

    // Get Cached File Name
    private function cachename ($name) {
     	return md5 ($this->__name . '_' . $name);
    }

}
//

//
// MemCached Inner Module
class MemCacheModule {

    private $__pf;
	private $__name;
    private $__mc;
    private $__exp;
    private $__open = true;
	private $__data = array ();
    private $__mods = array ();

    // Setting Up Variables
    public function __construct ( $prefix, $name, $mc, $expire ) {

		$this->__pf		= $prefix;
        $this->__name 	= $name;
		$this->__mc 	= $mc;
		$this->__exp	= $expire;
        $this->__open 	= true;
        $this->__data 	= array ();
        $this->__mods	= array ();

    }

    // Saving the Contents
    public function __destruct () {
		$this->__close();
    }

    public function __close() {

		if ( $this->__open ) {

			$mc = $this->__mc;
			$ex = $this->__exp;
	    	foreach ( $this->__mods as $m => &$t ) {
	    		$d = serialize( $this->__data[$m] );
				$mc->set( $this->cachename ($m), $d, 0, $ex );
	        }
	        $this->__open = false;

        }

    }

    // Getting Cached Variable
    public function __get ($name) {

    	if (!isset($this->__data[$name])) {
			$this->load ($name);
        }

        return $this->__data[$name];

    }

    public function __isset ( $name ) {
    	if (!isset($this->__data[$name])) {
			$this->load ($name);
        }

        return isset($this->__data[$name]);

    }

    // Setting Cache Variable Data
    public function __set ($name, $value) {

    	if (!isset($this->__data[$name])) {
			$this->load ($name);
        }

		$this->__data[$name] = $value;
        $this->__mods[$name] = true;

    }

    // Loading Data Block
    private function load ($name) {

		if ( $d = $this->__mc->get( $this->cachename ($name) ) ) {
	        $this->__data[$name] = unserialize ($d);
        } else $this->__data[$name] = null;

    }

    // Delete Cache Data
	public function clear ($name) {
//        $this->__mc->delete( $this->cachename( $name ), 0 );
        $this->__mc->replace( $this->cachename( $name ), '', 0, 1 );
		$this->__data[$name] = null;
    }

    // Get Cached File Name
    private function cachename ($name) {
     	return $this->__pf . md5 ($this->__name . '_' . $name);
    }

}
//

?>