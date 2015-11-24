<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / email.php
 *  Description:	E-Mail sending class
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

class eMailSender {
	private $core;

	public function __construct( $core ) {    	$this->core = $core;
	}

	public function __destruct() { }

	// Email Sending Function
	public function send( $to, $title, $text, $files = array() ) {
		$smtp	= defined( 'MAIL_SERVER' ) ? 1 : 0;
		$from	= MAIL_FROM;
		$title	= '=?UTF-8?B?'.base64_encode($title).'?=';
		if ( ! is_array( $to ) ) $to = array( $to );

		if ( $smtp ) {           	foreach ( $to as $t ) $this->send_smtp( $from, $t, $title, $text, $files );
		} else foreach ( $to as $t ) $this->send_mail( $from, $t, $title, $text, $files );

	}

	// Generic Emailer, $to is a string
	private function send_mail( $from, $to, $title, $text, $files ) {
		$type = strpos( $text, '<' ) !== false ? 'html' : 'plain';		if ( $files ) {			$boundary = '---'.md5(microtime()).'---';
			$headers = 'From: '.$from."\nContent-Type: multipart/mixed; boundary=\"$boundary\"";
			$body = "--$boundary\nContent-Type: text/plain; charset=utf-8\n".$text."\n--$boundary\n";
			foreach ( $files as $path => $name ) {
				$data = file_get_contents( $path );
				$body .= "Content-Type: application/octet-stream; name==?utf-8?B?".base64_encode( $name )."?=\n";
				$body .= "Content-Transfer-Encoding: base64\n";
				$body .= "Content-Disposition: attachment; filename==?utf-8?B?".base64_encode( $name )."?=\n\n";
				$body .= chunk_split(base64_encode( $data ))."\n--".$boundary ."--\n";
				unset ( $data );
			}
			$result = mail( $to, $title, $body, $headers );
		} else return mail( $to, $title, $text, "From: $from\nContent-Type: text/$type; charset=UTF-8" );
	}

	// SMTP-multimailer, $to is an array of string email addresses
	private function send_smtp( $from, $to, $title, $text, $files ) {
		$server = MAIL_SERVER;
		$port	= MAIL_PORT;
		$user	= MAIL_USER;
		$pass	= MAIL_PASS;
		$type = strpos( $text, '<' ) !== false ? 'html' : 'plain';

        $socket = fsockopen ( $server, $port, $errno, $errstr, 30 );
	    if ( defined( 'MAIL_DOMAIN' ) ) {	    	fputs($socket, "EHLO ".MAIL_DOMAIN."\r\n");
	    } else fputs($socket, "EHLO\r\n");
        if ( $this->_responce( $socket ) != '220' ) return $this->_stop ( $socket, __LINE__ );
	    fputs($socket, "AUTH LOGIN\r\n");
        if ( $this->_responce( $socket ) != '250' ) return $this->_stop ( $socket, __LINE__ );
	    fputs($socket, base64_encode($user) . "\r\n");
        if ( $this->_responce( $socket ) != '334' ) return $this->_stop ( $socket, __LINE__ );
	    fputs($socket, base64_encode($pass) . "\r\n");
        if ( $this->_responce( $socket ) != '334' ) return $this->_stop ( $socket, __LINE__ );
        if ( $this->_responce( $socket ) != '235' ) return $this->_stop ( $socket, __LINE__ );
	    fputs($socket, "MAIL FROM:<$from>\r\n");
        if ( $this->_responce( $socket ) != '250' ) return $this->_stop ( $socket, __LINE__ );
	    fputs($socket, "RCPT TO:<$to>\r\n");
        if ( $this->_responce( $socket ) != '250' ) return $this->_stop ( $socket, __LINE__ );
	    fputs($socket, "DATA\r\n");
        if ( $this->_responce( $socket ) != '354' ) return $this->_stop ( $socket, __LINE__ );
	    fputs($socket, "To: $to\r\n");
	    fputs($socket, "From: $from\r\n");
	    fputs($socket, "Subject: $title\r\n");
		if ( $files ) {			$boundary = '---'.md5(microtime()).'---'; //Разделитель
		    fputs($socket, "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n");
		    fputs($socket, "Content-Transfer-Encoding: 8bit\r\n");
		    fputs($socket, "\r\n\r\n");
		    fputs($socket, "--$boundary\r\nContent-Type: text/$type; charset=utf-8\r\n\r\n".$text."\r\n--$boundary" );
			foreach ( $files as $path => $name ) {
				$data = file_get_contents( $path );
				fputs($socket, "\r\nContent-Type: application/octet-stream; name==?utf-8?B?".base64_encode( $name )."?=\r\n" );
				fputs($socket, "Content-Transfer-Encoding: base64\r\n" );
				fputs($socket, "Content-Disposition: attachment; filename==?utf-8?B?".base64_encode( $name )."?=\r\n\r\n" );
				fputs($socket, chunk_split(base64_encode( $data ))."\r\n--".$boundary );
				unset ( $data );
			}
		    fputs($socket, "--\r\n.\r\n");
		} else {
		    fputs($socket, "Content-Type: text/$type; charset=UTF-8\r\n");
		    fputs($socket, "Content-Transfer-Encoding: 8bit\r\n");
		    fputs($socket, "\r\n\r\n");
		    fputs($socket, $text." \r\n");
		    fputs($socket, ".\r\n");
	    }
        if ( $this->_responce( $socket ) != '250' ) return _stop ( $socket, __LINE__ );
	    fputs($socket, "QUIT\r\n");
        fclose ($socket);

        return true;

	}

   	private function _responce ( $socket ) {
	    $server_response = '';
	    $resp = '';
	    while (substr($server_response, 3, 1) != ' ') {
	        if ( $server_response = fgets($socket, 128) ) {
				$resp .= $server_response;
	        } else return false;
	    }
    	return substr ($resp, 0, 3);
	}

	private function _stop ( $socket, $line ) {		fclose( $socket );
		return false;
	}

}

// end. =)