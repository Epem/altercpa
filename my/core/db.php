<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / db.php
 *  Description:	MySQLi database class
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

class sql_db {

	// Connection parameters
    private		$conid;
	private		$auth;

	// Statistics
	public		$status;
    public 		$queries;
    public		$log = array();

    // Setup authentication data
    public function __construct ( $sqlserver, $sqluser, $sqlpassword, $database, $charset = null, $collate = null ) {

    	$this->status = false;
        $this->queries = 0;
        $this->conid = null;

        $this->auth = array (
        	'server'	=> $sqlserver,
        	'login'		=> $sqluser,
        	'pass'		=> $sqlpassword,
        	'db'		=> $database,
        	'charset'	=> $charset,
        	'collate'	=> $collate,
        );

	}

	// Close database connection
	public function __destruct () {
		if ( $this->conid ) {
			return @mysqli_close($this->conid);
		} else return false;
	}

	// Connect to Database
	private function connect() {

		if ( $this->conid ) return true;
		$this->conid = mysqli_connect( $this->auth['server'], $this->auth['login'], $this->auth['pass'], $this->auth['db'] );

		if ( $this->conid ) {

			if ( $this->auth['charset'] && $this->auth['collate'] ) {
                mysqli_query ( $this->conid, "set character_set_client='".$this->auth['charset']."', character_set_results='".$this->auth['charset']."', collation_connection='".$this->auth['collate']."'" );
			} elseif ( $this->auth['charset'] ) {
                mysqli_query ( $this->conid, "set character_set_client='".$this->auth['charset']."', character_set_results='".$this->auth['charset']."'" );
			} elseif ( $this->auth['collate'] ) {
                mysqli_query ( $this->conid, "set collation_connection='".$this->auth['collate']."'" );
   			} else mysqli_set_charset( $this->conid, 'utf8' );

			return $this->status = true;

		} else return false;

	}

	//
    // Main Query Functions
    //

    // Process the Query
	public function query ( $sql, $use = false ) {

		if ( ! $this->status ) {
			if ( ! $this->connect() ) die( 'Database connection error!' );
		}

		if ( $sql ) {
        	$this->queries ++;
        	if (defined( 'SQL_LOG' )) $this->log[] = $sql;
			return mysqli_query ( $this->conid, $sql, $use ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT );
		} else return false;

	}

	// Get single Field
    public function field ( $sql ) {

	    if ( $result = $this->query( $sql, true ) ) {
			$ret = mysqli_fetch_row ( $result );
			mysqli_free_result( $result );
	        return $ret[0];
	    } else return false;

    }

	// Get column of Fields
    public function col ( $sql ) {

	    if ( $result = $this->query( $sql, true ) ) {
			$ret = array();
			while ( $r = mysqli_fetch_row ( $result ) ) $ret[] = $r[0];
 			mysqli_free_result( $result );
	        return $ret;
	    } else return false;

    }

	// Get identified column of Fields
    public function icol ($sql) {

	    if ( $result = $this->query( $sql, true ) ) {
			$ret = array();
			while ( $_r = mysqli_fetch_row( $result ) ) $ret[ $_r[0] ] = $_r[1];
 			mysqli_free_result( $result );
	        return $ret;
	    } else return false;

    }

	// Get one Row
	public function row ($sql) {

	    if ( $result = $this->query( $sql, true ) ) {
			$ret = mysqli_fetch_assoc ( $result );
			mysqli_free_result( $result );
	        return $ret;
	    } else return false;

	}

	// Get the Full Data Array
	public function data ( $sql ) {

	    if ( $result = $this->query( $sql, true ) ) {
//			$ret = array();
//			while ( $r = mysqli_fetch_assoc ( $result ) ) $ret[] = $r;
			$ret = mysqli_fetch_all ( $result, MYSQLI_ASSOC );
			@mysqli_free_result( $result );
	        return $ret;
	    } else return false;

	}

	//
	// Query Cicle start ->( one )-> end
	//

	// Start query, $db->query wrapper
	public function start( $sql ) {
    	return $this->query( $sql );
 	}

	// Get one row from block
	public function one( $query ) {

		if ( $query ) {
			return mysqli_fetch_assoc( $query );
		} else return false;

	}

	// Free the query result
	public function stop( $query ) {

		if ( $query ) {
			return mysqli_free_result( $query );
		} else return false;

	}

	//
	// Basic Wrappers
	//

	// Insert
	public function add( $table, $data ) {
		$sql = "INSERT INTO `$table` ( `" . implode( '`, `', array_keys( $data )) . "` ) VALUES ( '".implode( "', '", $data ) . "' )";
		return $this->query( $sql );
	}

	// Replace
	public function replace( $table, $data ) {
		$sql = "REPLACE INTO `$table` ( `" . implode( '`, `', array_keys( $data )) . "` ) VALUES ( '".implode( "', '", $data ) . "' )";
		return $this->query( $sql );
	}

	// Update
	public function edit( $table, $data, $where ) {

		if (is_array( $where )) {
			$w = array();
			foreach ( $where as $i => $v ) $w[] = "`$i` = '$v'";
			$where = implode ( ' AND ', $w );
		}

		$sql = "UPDATE `$table` SET ";
		foreach ( $data as $i => &$d ) {
			$sql .= " `$i` = '$d', ";
		} unset ( $d );
		$sql = substr( $sql, 0, -2 ) . " WHERE $where";
		return $this->query( $sql );

	}

	// Delete
	public function del( $table, $where ) {

		if (is_array( $where )) {
			$w = array();
			foreach ( $where as $i => $v ) $w[] = "`$i` = '$v'";
			$where = implode ( ' AND ', $w );
		}

		$sql = "DELETE FROM `$table` WHERE $where";
		return $this->query( $sql );

	}

	//
	// Misc Functions
	//

	// Last Insert ID
	public function lastid () {
		if ( ! $this->status ) if ( !$this->connect() ) die( 'Database connection error!' );;
		return mysqli_insert_id( $this->conid );
	}

} // class sql_db

?>