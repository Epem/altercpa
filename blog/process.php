<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			spacing zone / process.php
 *  Description:	PreLanding cleanup script
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

// Configuration
$blog = 'blog.easycpa.biz';
$oid = 2;	// Offer ID
$cid = 1;	// Company ID
$links = '<? footer(); ?>'; // Footer includes
// DO NOT EDIT BELOW THIS LINE

// Preparing SQL file to write to
define ( 'PATH', dirname( __FILE__ ) . '/' );
$ftw = fopen( PATH . time() . '.sql', 'w' );
fwrite ( $ftw, 'INSERT INTO cpa_site ( `site_url`, `offer_id`, `comp_id`, `site_type`, `site_key` ) VALUES ' );
$first = true;

// Walking through directory
$d = opendir ( PATH );
while ( $f = readdir ( $d ) ) {
	if ( $f != '.' && $f != '..' && is_dir ( PATH . $f ) ) {
		if (file_exists(  PATH . $f . '/index.html' )) {

			// Preparing index file contents
			$data = file_get_contents( PATH . $f . '/index.html' );
			$data = '<?php include "../offer'.$oid.'.php"; $url = ourl(); ?>' . $data;

			// Fixing bad URLs without HREF
			preg_match_all( '#<a([^>]+)>#msi', $data, $ms );
			$ass = array_unique( $ms[0] );
			foreach ( $ass as $a ) if ( strpos( $a, 'href' ) === false ) $data = str_replace( $a, '<a href="" '.substr( $a, 2 ), $data );

			// Cleaning up URLS and bad data
			$data = preg_replace( '#<a([^>]+)href="(.*?)"#si', '<a $1 href="<?=$url;?>"', $data );
			$data = preg_replace( '#<a([^>]+)href=\'(.*?)\'#si', '<a $1 href=\'<?=$url;?>\'', $data );
			$data = preg_replace( '#onclick="(.*?)"#si', '', $data );
			$data = preg_replace( '#<script(.*?)</script>#si', '', $data );
			$data = preg_replace( '#<noscript(.*?)</noscript>#si', '', $data );
			$data = str_replace ( '<?xml version="1.0" encoding="UTF-8"?>', '', $data );

			// Adding footer
			if ( stripos( $data, '</body>' ) !== false ) {
				$data = str_ireplace( '</body>', $links.'</body>', $data );
			} elseif ( stripos( $data, '</html>' ) !== false ) {
				$data = str_ireplace( '</html>', $links.'</body></html>', $data );
			} else $data .= $links.'</body></html>';

			// Saving results to SQL
			if ( $first ) {
				fwrite ( $ftw, "( '$blog/$f', '$oid', '$cid', '1', '".md5( microtime() . rand( 0, 1000 ) )."' )" );
				$first = false;
			} else fwrite ( $ftw, ", ( '$blog/$f', '$oid', '$cid', '1', '".md5( microtime() . rand( 0, 1000 ) )."' )" );

			// Saving the main file
			file_put_contents( PATH . $f . '/index.php', $data );
			unlink( PATH . $f . '/index.html' );

		}
	}
} closedir( $d );
fclose( $ftw );