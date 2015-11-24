<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / texter.php
 *  Description:	Text processings
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

class texter {

	private $core;
	private $shortcode;

	public function __construct ( $core ) {
		$this->core = $core;
		$this->shortcode = array ();
    }

    public function __destruct () { }

	//
	// Encoding
	//

	// Encoding simple text line
	public function line ( $text ) {
		$text = trim ( $text );
		$text = htmlspecialchars ( $text );

		if ( $this->core->cando('texter_line') ) {
			return $this->core->filter ( 'texter_line', $text );
		} else return $text;

	}

	// Encoding text width HTML-code
	public function code ( $text ) {
		if ( $this->core->cando('texter_code') ) {
			return $this->core->filter ( 'texter_code', $text );
		} else return $text;

	}

	// Password
	public function pass ( $text ) {
		$text = trim ( $text );
		$text = md5 ( $text );

		if ( $this->core->cando('texter_pass') ) {
			return $this->core->filter ( 'texter_pass', $text );
		} else return $text;

	}

	// Creating link from text
	public function link ( $text ) {
		$text = trim ( $text );

		$code = array(
			"Є"=>"YE","І"=>"I","Ѓ"=>"G","і"=>"i","№"=>"#","є"=>"ye","ѓ"=>"g",
			"А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
			"Е"=>"E","Ё"=>"YO","Ж"=>"ZH",
			"З"=>"Z","И"=>"I","Й"=>"J","К"=>"K","Л"=>"L",
			"М"=>"M","Н"=>"N","О"=>"O","П"=>"P","Р"=>"R",
			"С"=>"S","Т"=>"T","У"=>"U","Ф"=>"F","Х"=>"X",
			"Ц"=>"C","Ч"=>"CH","Ш"=>"SH","Щ"=>"SHH","Ъ"=>"'",
			"Ы"=>"Y","Ь"=>"","Э"=>"E","Ю"=>"YU","Я"=>"YA",
			"а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d",
			"е"=>"e","ё"=>"yo","ж"=>"zh",
			"з"=>"z","и"=>"i","й"=>"j","к"=>"k","л"=>"l",
			"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
			"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"x",
			"ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"shh","ъ"=>"",
			"ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya","—"=>"-",
		);

		$text = strtr ( $text, $code );
		$text = preg_replace ("#([\s\-]+)#si", '-', $text);
		$text = trim ( $text, '-' );
		$text = strtolower ( $text );
		$text = preg_replace ("#([^a-z0-9\-\_\.]*)#si", '', $text);

		if ( $this->core->cando('texter_link') ) {
			return $this->core->filter ( 'texter_link', $text );
		} else return $text;

	}

	// Checking the URL
	public function url ( $url ) {		if ( $url ) {			$url = parse_url( $url );
			if ( !in_array( $url['scheme'], array( 'http', 'https', 'ftp', '' ))) return '';
            $url['path'] = strtr( urlencode( $url['path'] ), array( '%2F' => '/', '%7E' => '~', '%24' => '$', '%2C' => ',', '%25' => '%' ) );
			$url = $this->unparse_url( $url );//			return $url;
			$urlregex = "^(https?|ftp)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$\%\~_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
			return (eregi($urlregex, $url)) ? $url : '';
		} else return '';
	}

	private function unparse_url($parsed_url) {
		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
		$host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
		$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
		$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
		$pass     = ($user || $pass) ? "$pass@" : '';
		$path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
		$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
		return "$scheme$user$pass$host$port$path$query$fragment";
	}

	// Checking the E-Mail
	public function email ( $email ) {		$email = strtolower(trim( $email ));
		if ( $email ) {
			$mailregex = "#^[a-z0-9\._-]+@[a-z0-9][a-z0-9_-]*(\.[a-z0-9_-]+)*\.([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel)$#i";
			return (preg_match($mailregex, $email)) ? $email : '';
		} else return '';
	}

	//
	// Decoding
	//

	// Separate lines with "p" and "br"
    public function lines ($message) {

	    $message = preg_replace("#\n([\s]*)\n#si", "</p><p>",	$message);
	    $message = preg_replace("#<p>([\s]*)</p>#si", '',	$message);
    	$message = strtr($message, array("\r" => '', "\n" => '<br />'));

		$message = '<p>' . $message . '</p>';

		$message = $this->links ( $message );

		if ( $this->core->cando('texter_lines') ) {
			return $this->core->filter ( 'texter_lines', $message );
		} else return $message;

    }

	// Make links clickable
    public function links ($text) {

	    $text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1&#058;", $text);
	    $text = ' ' . $text . ' ';
	    $text = preg_replace("#(^|[\s>])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $text);
	    $text = preg_replace("#(^|[\s>])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $text);
	    $text = preg_replace("#(^|[\s>])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $text);
		$text = substr( $text, 1, -1 );

		if ( $this->core->cando('texter_links') ) {
			return $this->core->filter ( 'texter_links', $text );
		} else return $text;

    }

	// ShortCode Processor - WordPress Based
    public function shortcodes( $text ) {
		// Checking for Codes
		if (empty( $this->shortcode ) || !is_array( $this->shortcode ))
			return $text;

		// Tag Search RegExp
		$tagnames = array_keys( $this->shortcode );
		$tagregexp = join( '|', array_map('preg_quote', $tagnames) );
		$tagregexp = '(.?)\[('.$tagregexp.')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)';
		return preg_replace_callback('/'.$tagregexp.'/s', 'shortcodes', $text );

    }

    public function addcode ( $code, $func ) {		$this->shortcode[ $code ] = $func;
    }

    public function getcode ( $code ) {    	return $this->shortcode[ $code ];
    }

	// Main Output Processing with ShortCodes
	public function out( $text, $shortcodes = true ) {
		// Starting Text Processors
		$text = $this->unurl( $text );
		$text = $this->shortcodes( $text );

		// Processing Cleanup
  	    $text = preg_replace( "#<p>([\s]*)</p>#si", '', $text );

		return $text;

	}

	// UnURL
	public function unurl( $text ) {
		$host = strtr($this->core->config->uri['host'], array('.' => '\.'));
		$pattern = '/<a (.*?)href=[\"\']([a-z0-9]+)\:\/\/(?!'.$host.')(.*?)\/?(.*?)[\"\'](.*?)>(.*?)<\/a>/i';
		if ( $this->core->config->uri['mode'] ) {			$text = preg_replace_callback( $pattern, 'text_unurl', $text );
		} else $text = preg_replace_callback( $pattern, 'text_unurl_bad', $text );
		return $text;

	}

	public function extlink( $url ) {
		$url = trim( $url );		if ($this->core->config->uri['mode'] ) {	    	$url = explode( '://', $url, 2 );
			return '/goto/' . $url[0] . '/' . $url[1];
		} else return '/?goto=' . $url;
	}

	//
	// Text Cuts
	//

	function excerpt ( $text, $len = 100 ) {
		$text = strip_tags ( $text );
		if ( mb_strlen ( $text ) > $len ) {        	$text = mb_substr ( $text, 0, $len );
        	if ( mb_strpos($text, ' ') ) $text = mb_substr ( $text, 0, mb_strrpos ( $text, ' ' ) );
			$text = trim ( $text );
			if ( $text ) $text .= ' [...]';
		} else $text = trim ( $text );

		return $text;

	}

	//
	// Deprecated Statics
	//

	// Deprecated Text Encode
    public static function encode ($text, $level, $length = 0) {

		$text = trim ($text);

        if ($length) $text = mb_substr ($text, 0, $length);

        switch ($level) {

          case 'text':
          	$text = htmlspecialchars($text);
            break;

          case 'pass':
          	$text = preg_replace ("#([^a-z0-9\-\_]*)#si", '', $text);
			$text = md5($text);
            break;

          case 'link':
    	  	$text = preg_replace ("#([^a-z0-9\-\_\.]*)#si", '', $text);
            break;

        }

        return $text;

    }

	// Deprecated Text Decode
    public static function decode ( $message, $level ) {

		switch ($level) {

          case 'bb2':
		    $message = preg_replace("#\n([\s]*)\n#si", "</p><p>",	$message);
		    $message = preg_replace("#<p>([\s]*)</p>#si", '',	$message);
	    	$message = strtr($message, array("\r" => '', "\n" => '<br />'));
			$message = '<p>' . $message . '</p>';

          case 'link':
		    $message = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1&#058;", $message);
		    $message = ' ' . $message;
		    $message = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $message);
		    $message = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $message);
		    $message = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $message);
		    $message = substr($message, 1);

        }

        return $message;

    }

}

function text_unurl( $text ) {
	return '<a ' . $text[1] . 'href="/goto/' . $text[2] . '/' . $text[3] . '/' . $text[4] . '"' . $text[5] . '>' . $text[6] . '</a>';
}

function text_unurl_bad( $text ) {
	return '<a ' . $text[1] . 'href="/?goto=' . $text[2] . '://' . $text[3] . '/' . $text[4] . '"' . $text[5] . '>' . $text[6] . '</a>';
}

function shortcodes( $m ) {
	global $core;

	// allow [[foo]] syntax for escaping a tag
	if ( $m[1] == '[' && $m[6] == ']' ) {
		return substr($m[0], 1, -1);
	}

	$process = $core->text->getcode( $m[2] );
	$attrtext = $m[3];

	$atts = array();
	$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
	$attrtext = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $attrtext);
	if ( preg_match_all( $pattern, $attrtext, $match, PREG_SET_ORDER ) ) {
		foreach ($match as $ms) {
			if (!empty($ms[1]))
				$atts[strtolower($ms[1])] = stripcslashes($ms[2]);
			elseif (!empty($ms[3]))
				$atts[strtolower($ms[3])] = stripcslashes($ms[4]);
			elseif (!empty($ms[5]))
				$atts[strtolower($ms[5])] = stripcslashes($ms[6]);
			elseif (isset($ms[7]) and strlen($ms[7]))
				$atts[] = stripcslashes($ms[7]);
			elseif (isset($ms[8]))
				$atts[] = stripcslashes($ms[8]);
		}
	} else {
		$atts = ltrim($text);
	}

	if ( isset( $m[5] ) ) {
		return $m[1] . call_user_func( $process, $core, $atts, $m[5] ) . $m[6];
	} else return $m[1] . call_user_func( $process, $core, $atts, NULL ) . $m[6];

}

?>