<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / api.php
 *  Description:	Common functions
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
// Parameters Extraction
//

function params ( $core, $list ) {

	$p = array();

	foreach ( $list as $i => $n ) {

		if ( is_int( $i ) ) {

			if ( isset( $core->post[$n] ) && $core->post[$n] != '' ) {
				$p[$n] = (int) $core->post[$n];
			} elseif ( isset( $core->get[$n] ) && $core->get[$n] != '' ) {
				$p[$n] = (int) $core->get[$n];
			} else $p[$n] = false;

		} else {

			switch ( $n ) {

			  case 'date':
				if ( isset( $core->post[$i] ) && $core->post[$i] != '' ) {
					$p[$i] = form2date( $core->post[$i] );
				} elseif ( isset( $core->get[$i] ) && $core->get[$i] != '' ) {
					$p[$i] = form2date( $core->get[$i] );
				} else $p[$i] = false; break;

			  case 'email':
				if ( isset( $core->post[$i] ) && $core->post[$i] != '' ) {
					$p[$i] = $core->text->email( $core->post[$i] );
				} elseif ( isset( $core->get[$i] ) && $core->get[$i] != '' ) {
					$p[$i] = $core->text->email( $core->get[$i] );
				} else $p[$i] = false; break;

			  default:
				if ( isset( $core->post[$i] ) && $core->post[$i] != '' ) {
					$p[$i] = $core->text->line( $core->post[$i] );
				} elseif ( isset( $core->get[$i] ) && $core->get[$i] != '' ) {
					$p[$i] = $core->text->line( $core->get[$i] );
				} else $p[$i] = false; break;

			}

		}

	}

	return $p;

}

//
// XML processing
//

// Convert array to XML
function array2xml ( $array, $prev, $xml = false ) {
    if ( $xml === false )  $xml = new SimpleXMLElement('<result/>');
    foreach ( $array as $key => $value ) {
        if ( is_array( $value ) ) {
            if( is_numeric( $key ) ){
                array2xml( $value, $prev, $xml->addChild( $prev ) );
            } else array2xml( $value, $key, $xml->addChild( $key ) );
        } else $xml->addChild( $key, $value );
    }
    return $xml->asXML();
}

//
// IP Routines
//

function encode_ip($dotquad_ip) {
	$ip_sep = explode('.', $dotquad_ip);
	return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
}

function decode_ip($int_ip) {
	$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
	return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
}

function ip2int ( $ip ) {
	return sprintf( "%u", ip2long( $ip ) );
}

function int2ip ( $ip ) {
	return long2ip( $ip );
}

function remoteip ( $server ) {

	if ( $xff = $server['HTTP_X_FORWARDED_FOR'] ) {
		$xffd = explode( '.', $xff );
		if (!(
			$xffd[0] == 10 ||
			( $xffd[0] == 172 && $xffd[1] > 15 && $xffd[1] < 33 ) ||
			( $xffd[0] == 192 && $xffd[0] == 168 ) ||
			( $xffd[0] == 169 && $xffd[0] == 254 )
		)) return $xff;
	}

	if ( $ff = $server['HTTP_FORWARDED'] ) {
		$xff = trim( str_replace( 'for=', '', $ff ), '"' );
		if ( strpos( $xff, ':' ) !== false ) {
			$xffa = explode( ':', $xff );
			$xff = $xffa[0];
		}
		$xffd = explode( '.', $xff );
		if (!(
			$xffd[0] == 10 ||
			( $xffd[0] == 172 && $xffd[1] > 15 && $xffd[1] < 33 ) ||
			( $xffd[0] == 192 && $xffd[0] == 168 ) ||
			( $xffd[0] == 169 && $xffd[0] == 254 )
		)) return $xff;
	}

	if ( $xff = $server['HTTP_CLIENT_IP'] ) {
		$xffd = explode( '.', $xff );
		if (!(
			$xffd[0] == 10 ||
			( $xffd[0] == 172 && $xffd[1] > 15 && $xffd[1] < 33 ) ||
			( $xffd[0] == 192 && $xffd[0] == 168 ) ||
			( $xffd[0] == 169 && $xffd[0] == 254 )
		)) return $xff;
	}

	return $server['REMOTE_ADDR'];

}

//
// Pagination
//

function pages ($linkex, $items, $onpage, $page) {

    $sp =  (strpos($linkex, '?') === false) ? '?' : '&';

	$pages = ceil ( $items / $onpage );
    if ($pages < 2) return '';

	$block1st = 1;
    $block1en = min (2, $pages);
    $block2st = max ($block1en + 1, min ($page - 2, $pages - 4));
    $block2en = min ($pages, $block2st + 4);
    $block3st = max ($block2en + 1, $pages - 1);
    $block3en = $pages;

    if ($page > 1) {
		$pageline .= ' <a href="' . $linkex . $sp . 'page=' . ($page - 1).'">&laquo;</a> ';
    } else $pageline .= " <b>&laquo;</b> ";

    for ($i = $block1st; $i <= $block1en; $i++) {
        $linkto = ($i > 1) ? $linkex . $sp . "page=$i" : $linkex;
        if ($i == $page) {
        	$pageline .= " <b>$i</b> ";
        } else {
        	$pageline .= ' <a href="' . $linkto . '">' . $i . '</a> ';
        }
    }

	if ($i < $block2st) $pageline .= '...';

    for ($i = $block2st; $i <= $block2en; $i++) {
        $linkto = $linkex . $sp . "page=$i";
        if ($i == $page) {
            $pageline .= " <b>$i</b> ";
        } else {
            $pageline .= ' <a href="' . $linkto . '">' . $i . '</a> ';
        }
    }

    if ($i < $block3st) $pageline .= '...';

    for ($i = $block3st; $i <= $block3en; $i++) {
        $linkto = $linkex . $sp . "page=$i";
        if ($i == $page) {
            $pageline .= " <b>$i</b> ";
        } else {
            $pageline .= ' <a href="' . $linkto . '">' . $i . '</a> ';
        }
    }

    if ($page < $pages) {
		$pageline .= ' <a href="' . $linkex . $sp . 'page=' . ($page + 1).'">&raquo;</a> ';
    } else $pageline .= " <b>&raquo;</b> ";

	return $pageline;

}

//
// Quotes in Loading
//

function stripslashes_deep($value) {
	$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
	return $value;
}

function add_magic_quotes( $array ) {

	foreach ( (array) $array as $k => $v ) {
		if ( is_array( $v ) ) {
			$array[$k] = add_magic_quotes( $v );
		} else {
			$array[$k] = escape( $v );
		}
	}
	return $array;
}

function escape($data) {
    if ( is_array($data) ) {
        foreach ( (array) $data as $k => $v ) {
            if ( is_array($v) )
                $data[$k] = escape( $v );
            else
                $data[$k] = addslashes( $v );
        }
    } else {
        $data = addslashes( $data );
    }

    return $data;
}

//
// Unknown shit ...
//

// Ouputs MB or KB if needed
function mkb_out($mkb_in) {
	if ($mkb_in > 1024 * 1024) return sprintf("%1.2f Mb", ($mkb_in/1024/1024));
	if ($mkb_in > 1024) return sprintf("%1.2f kb", ($mkb_in/1024));
    return ($mkb_in) ? $mkb_in : 0;
}

function makedate($tim, $dateformat = "%a %d %B %Y %H:%M:%S") {

	global $config;

	return strftime($dateformat, $tim + $config['tc']);

}

function smartdate($tim) {

	global $lang;

	if ($tim>(time()-60*60*24) && (date("d", $tim)==date("d", time()))) return $lang['today'].date("H:i", $tim);
	if ($tim>(time()-2*60*60*24) && (date("d", $tim)==date("d", time())-1)) return $lang['yesterday'].date("H:i", $tim);
	return date("d.m.y H:i", $tim);

}

function timeleft( $tim, $nw = 0 ) {

	global $lang;

	$nw = $nw ? $nw : time();
	$tim -= $nw;

	if ( $tim > 0 ) {
		$m = round( $tim / 60 );
		$h = round( $m / 60 );
		$d = round( $h / 24 );
		$m = $m % 60;
		$h = $h % 24;
		return ( $d ? $d.' дн. ' : '' ) . sprintf( '%02d', $h ).':'. sprintf( '%02d', $m );
	} else return '';

}

// Converting form post data to generic date
function form2date ( $date ) {

	$date = preg_replace( '#([^0-9\-])+#i', '', $date );
	$date = explode( '-', $date );
	return sprintf( "%04d%02d%02d", $date[0], $date[1], $date[2] );

}

// Convert Inside-Date into Form format
function date2form ( $date = '00000000' ) {

	$y = (int) substr( $date, 0, 4 );
	$m = (int) substr( $date, 4, 2 );
	$d = (int) substr( $date, 6, 2 );
	return sprintf( "%04d-%02d-%02d", $y, $m, $d );

}

// Show date() for inside date format
function date2show ( $format, $date ) {

	$y = (int) substr( $date, 0, 4 );
	$m = (int) substr( $date, 4, 2 );
	$d = (int) substr( $date, 6, 2 );
	$date = mktime( 0, 0, 0, $m, $d, $y );
	return date( $format, $date );

}

//
// Multy-Bite Uppercase Words if not exists
if (!function_exists('mb_ucwords')) {
	function mb_ucwords( $text, $cp = 'UTF-8' ) {
		$text = explode( ' ', $text );
		$t = '';
		foreach ( $text as &$o ) {
			if ( ($lng = mb_strlen( $o, $cp )) > 1 ) {
				$t .= ' ' . mb_strtoupper( mb_substr( $o, 0, 1, $cp ), $cp ) . mb_strtolower( mb_substr( $o, 1, $lng-1, $cp ), $cp );
			} else $t .= ' ' . mb_strtoupper( $o, $cp );
		} unset ( $o );
		return ltrim( $t );
	}
}
//

// Weighted Random in array ( id => weight )
function wrand( &$data ) {

	$sm = array_sum( $data );
	$rn = rand( 0, $sm );

	$cc = 0;
	foreach ( $data as $d => $v ) {
       	if ( $rn >= $cc && $rn < $cc+$v ) {
           	return $d;
       	} else $cc += $v;
	} return $d;

}

function select ( $data, $cur = null, $fld = null ) {

	$result = array();

	foreach ( $data as $val => $name ) {
     	$result[] = array(
			'name'		=> $fld ? $name[$fld] : $name,
			'value'		=> $val,
			'select'	=> ( $cur !== null && $cur == $val ) ? true : false,
     	);
	}

	return $result;

}

//
// Money
//

function rur ( $m ) {
	$cl = ( $m < 0 ) ? 'red' : ( $m ? 'green' : 'grey' );
	return '<span class="rur ' . $cl . '">' . sprintf( '%0.2f', $m ) . '</span>';
}

function cent ( $m ) {
	$m = $m / 100;
	$cl = ( $m < 0 ) ? 'red' : ( $m ? 'green' : 'grey' );
	return '<span class="usd ' . $cl . '">' . sprintf( '%0.2f', $m ) . '</span>';
}

function usd ( $m ) {
	$cl = ( $m < 0 ) ? 'red' : ( $m ? 'green' : 'grey' );
	return '<span class="usd ' . $cl . '">' . sprintf( '%0.2f', $m ) . '</span>';
}

//
// CURL
//

function curl( $url, $post = array(), $config = array() ) {

	$curl = curl_init( $url );
	curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0' );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );

	if ( $post ) {
		curl_setopt( $curl, CURLOPT_POST, 1 );
    	curl_setopt( $curl, CURLOPT_POSTFIELDS, $post );
	}

	if ( $config['login'] && $config['pass'] ) {
		curl_setopt( $curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY );
		curl_setopt( $curl, CURLOPT_USERPWD, $config['login'].':'.$config['pass'] );
	}

	if ( $config['cookie'] ) {
		curl_setopt( $curl, CURLOPT_COOKIEFILE, $config['cookie'] );
		curl_setopt( $curl, CURLOPT_COOKIEJAR, $config['cookie'] );
	}

	$result = curl_exec( $curl );
	curl_close( $curl );
	return $result;

}

function geoip ( $core, $ip ) {

	$ip = sprintf( "%u", ip2long( $ip ) );
	if ( ! $ip ) return false;
	$ipd = $core->db->row( "SELECT * FROM `".DB_GEOIP."` WHERE `ip` < '$ip' ORDER BY `ip` DESC LIMIT 1" );
	if ( ! $ipd ) return false;
	$cid = $ipd['city'] ? $core->db->row( "SELECT * FROM `".DB_GEOCITY."` WHERE `id` = '".$ipd['city']."' LIMIT 1" ) : false;

	$result = array(
		'status'	=> 'ok',
		'ip'		=> long2ip( $ip ),
		'from'		=> long2ip( $ipd['ip'] ),
		'to'		=> long2ip( $ipd['last'] ),
		'country'	=> $ipd['country']
	);
	if ( $cid['city'] )		$result['city']		= $cid['city'];
	if ( $cid['region'] )	$result['region']	= $cid['region'];
	if ( $cid['district'] )	$result['district']	= $cid['district'];
	if ( $cid['lat'] )		$result['lat']		= $cid['lat'];
	if ( $cid['lng'] )		$result['lng']		= $cid['lng'];

	return $result;

}

// end. =)