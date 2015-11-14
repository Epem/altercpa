<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / mainline.php
 *  Description:	BreadCrumbs
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

class SiteMainline { // class sitecache start

	// Cache Data Variables
    private		$core;
	private 	$data = array();
    private		$elements = array ();

    public		$tsf = ' &ndash; ';
    public		$tsb = ' &ndash; ';
    public 		$msf = ' &raquo; ';
    public 		$msb = ' &laquo; ';

    private		$title_style = true;
    private		$mainline_style = true;

    // Constructor
	public function __construct ($core) {

    	// Setting Up Global Context Variable
        $this->core = $core;

        $this->data 			= array ();
        $this->elements 		= array ();

        $this->site_name	 	= $core->lang['site_name'];
        $this->site_descr 		= $core->lang['site_descr'];
        $this->site_url		 	= $core->lang['site_url'];
        $this->site_meta_k 		= $core->lang['site_meta_k'];
        $this->site_meta_d 		= $core->lang['site_meta_d'];
        $this->copyright 		= $core->lang['site_copyright'];

        return true;

	}

    // Destructor
    public function __destruct () { }

    // Get Cache Line
    public function __get ($variable) {
    	return $this->data[$variable];
    }

    // Update Cache Line
    public function __set ($variable, $value) {
    	$this->data[$variable] = $value;
    }

    //
    // Printing Functions
    //

    // Title Print
	public function title () {

		$title = $this->site_name;
        $c = count ($this->elements);

        if ($this->title_style) {
			for ($i = 0; $i < $c; $i++) {
				$title .= $this->tsf . $this->elements[$i][0];
            }
        } else {
			for ($i = $c-1; $i >= 0; $i--) {
				$title = $this->elements[$i][0] . $this->tsb . $title;
            }
        }

        return $title;

    }

    // Mainline Print
    public function mainline ( $short = null ) {

		$mainline = ( $short ) ? '' : $this->core->url ('index', $this->site_name);
        $c = count ($this->elements);

        if ($this->mainline_style) {
			for ($i = 0; $i < $c; $i++) {
				if ($this->elements[$i][1]) {
                	$mainline .= $this->msf . $this->core->url ('link', $this->elements[$i][1], $this->elements[$i][0]);
                } else $mainline .= $this->msf . $this->elements[$i][0];
            }
        } else {
			for ($i = $c-1; $i >= 0; $i--) {
				if ($this->elements[$i][1]) {
					$mainline = $this->core->url ('link', $this->elements[$i][1], $this->elements[$i][0]) . $this->msb . $mainline;
                } else $mainline = $this->elements[$i][0] . $this->msb . $mainline;
            }
        }

		if ( $short ) $mainline = substr ( $mainline, strlen($this->msf) );

        return $mainline;

    }

    // Printing Page Descr
    public function descr () {
    	return $this->site_meta_d;
    }

    // Printing Page Keywords
    public function keywords () {
    	return $this->site_meta_k;
    }

    // DownSite Copyright
    public function copyright () {
    	$copyright = sprintf ($this->copyright, $this->site_name, $this->site_url);
        return $copyright;
    }

    //
    // Adding Functions
    //

    // Add Element To The End
    public function add ($name, $url = null) {
		$this->elements[] = array ($name, $url);
    }

    // Add Element To The Beginning
    public function add_b ($name, $url = null) {
		array_unshift ($this->elements, array ($name, $url));
    }

    // Adding (Replacing) Description
    public function add_d ($d) {
    	$this->site_meta_d = $d;
    }

    // Add Keywords To Page
    public function add_k ($kw) {

    	$k = explode (',', $this->site_meta_k);
        for ($i = 0; $i < count($k); $i++) {
			$k[$i] = trim ($k[$i]);
        }

        if (is_array($kw)) {
			array_unshift ($k, $kw);
        } else {
	        $kw = explode (',', $kw);
	        for ($i = 0; $i < count($kw); $i++) {
	            $kw[$i] = trim ($kw[$i]);
	        }
			array_unshift ($k, $kw);
		}

        $this->site_meta_k = implode (', ', $k);

    }

} // class sitecache end

?>