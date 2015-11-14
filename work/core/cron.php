<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / cron.php
 *  Description:	Cron controller
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
class CronControl {

	private $core;
	private $task 		= array ();

	private $timetable	= array ();
	private $changed	= false;

    // Setting the Path
    public function __construct ( $core ) {
		$this->core			= $core;
		$this->task			= array ();
		$this->timetable	= $core->cache->main->cron;
		$this->chenged		= false;
    }

    // Clearing Inner Modules
    public function __destruct () {
		$this->process ();
    }

    public function add ( $name, $function, $period ) {
		$this->task[ $name ] = array(
  			'function'	=> $function,
  			'period'	=> $period,
		);

    }

    public function del ( $name ) {
		unset( $this->task[$name] );
		if ( isset($this->timetable[$name]) ) {			unset ( $this->timetable[$name] );
			$this->changed = true;
		}

    }

    public function process ( ) {
		foreach ( $this->task as $name => &$task ) {			$launch = time () - $task['period'];
			if ( empty($this->timetable[$name]) || $launch > $this->timetable[$name] ) {            	call_user_func( $task['function'], $this->core );
            	$this->timetable[$name] = time();
            	$this->changed = true;
			}
		} unset ( $t );

		// Updating Timetable
		if ( $this->changed ) {        	$this->core->cache->main->cron = $this->timetable;
		}

    }

}
//

?>