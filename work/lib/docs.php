<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / docs.php
 *  Description:	Documents related functions
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

// Document creation function
function docs_xls_make ( $ord, $comp, $out = false ) {
	// Load the Excel
	require_once PATH_LIB . 'PHPExcel/IOFactory.php';
	if ( $ord['paid_ok'] ) {
		$objPHPExcel = PHPExcel_IOFactory::load( PATH_DOCS . "paid.xls" );
	} else $objPHPExcel = PHPExcel_IOFactory::load( PATH_DOCS . "form.xls" );
	$objPHPExcel->setActiveSheetIndex(0);
	$aSheet = $objPHPExcel->getActiveSheet();

	docs_xls_fillall ( $aSheet, $ord, $comp );

	// Output the document
	$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel5' );
	if ( ! $out ) {
		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="order-'.$ord['order_id'].'.xls"');
		$objWriter->save( "php://output" );
	} else $objWriter->save( $out );
	$objPHPExcel->disconnectWorksheets();
	unset ( $aSheet, $objWriter, $objPHPExcel );

}

// Fill the blanks
function docs_xls_fillall ( $aSheet, $ord, $comp ) {
	//
	// BackPayment Form
	//

	// Company
	$aSheet->setCellValue( 'BA39', $comp['comp_fio'] );
	$aSheet->setCellValue( 'BA47', $comp['comp_addr'] );
	$aSheet->setCellValue( 'BP65', $comp['comp_bank'] );
    docs_xls_fill ( $aSheet, 61, 52, $comp['comp_inn'] );
    docs_xls_fill ( $aSheet, 61, 92, $comp['comp_ks'] );
    docs_xls_fill ( $aSheet, 69, 67, $comp['comp_acc'] );
    docs_xls_fill ( $aSheet, 69, 114, $comp['comp_bik'] );
	docs_xls_fill ( $aSheet, 55, 114, $comp['comp_index'], 3 );

	// Order
	$nn = explode( ' ', $ord['order_name'], 2 );
	$aSheet->setCellValue( 'BF93', $nn[0] );
	$aSheet->setCellValue( 'AV97', $nn[1] );
	docs_xls_fill ( $aSheet, 108, 114, $ord['order_index'], 3 );
	$aSheet->setCellValue( 'CV30', $ord['order_price'] );
	$aSheet->setCellValue( 'AV35', pricenum2str($ord['order_price']) );

	//
	// Sending Form
	//

	// Company
	$cn = explode( ' ', $comp['comp_fio'] );
	$l1 = $l2 = array(); $len = 0;
	foreach ( $cn as $c ) {
		if ( $len < 28 ) $l1[] = $c;
		else $l2[] = $c;
		$len += mb_strlen( $c ) + 1;
	}
	$aSheet->setCellValue( 'AM163', implode( ' ', $l1 ) );
	$aSheet->setCellValue( 'AC167', implode( ' ', $l2 ) );
	$ca = explode( ' ', $comp['comp_addr'] );
	$l1 = $l2 = $l3 = array(); $len = 0;
	foreach ( $ca as $c ) {
		if ( $len < 28 ) $l1[] = $c;
		elseif ( $len - $len2 < 36 ) $l2[] = $c;
		else $l3[] = $c;
		$len += mb_strlen( $c ) + 1;
		$len2 = mb_strlen(implode( ' ', $l1 ));
	}
	$aSheet->setCellValue( 'AM172', implode( ' ', $l1 ) );
	$aSheet->setCellValue( 'AC176', implode( ' ', $l2 ) );
	$aSheet->setCellValue( 'AC180', implode( ' ', $l3 ) );
	docs_xls_fill ( $aSheet, 183, 59, $comp['comp_index'], 3 );

	// Order
	$un = explode( ' ', $ord['order_name'] );
	$l1 = $l2 = array(); $len = 0;
	foreach ( $un as $c ) {
		if ( $len < 33 ) $l1[] = $c;
		else $l2[] = $c;
		$len += mb_strlen( $c ) + 1;
	}
	$aSheet->setCellValue( 'CL179', implode( ' ', $l1 ) );
	$aSheet->setCellValue( 'CF184', implode( ' ', $l2 ) );

	$addr = $ord['order_addr'];
	if ( $ord['order_street'] ) $addr = $ord['order_street'] . ', ' . $addr;
	if ( $ord['order_city'] ) $addr = $ord['order_city'] . ', ' . $addr;
	if ( $ord['order_area'] ) $addr = $ord['order_area'] . ', ' . $addr;
	$ua = explode( ' ', $addr );
	$l1 = $l2 = $l3 = array(); $len = 0;
	foreach ( $ua as $c ) {
		if ( $len < 33 ) $l1[] = $c;
		elseif ( $len - $len2 < 38 ) $l2[] = $c;
		else $l3[] = $c;
		$len += mb_strlen( $c ) + 1;
		$len2 = mb_strlen(implode( ' ', $l1 ));
	}
	$aSheet->setCellValue( 'CL189', implode( ' ', $l1 ) );
	$aSheet->setCellValue( 'CF193', implode( ' ', $l2 ) );
	$aSheet->setCellValue( 'CF197', implode( ' ', $l3 ) );
	docs_xls_fill ( $aSheet, 196, 117, $ord['order_index'], 3 );
	$prc_ru = round( $ord['order_price'] );
	$prc_kp = ( $ord['order_price'] * 100 ) % 100;
	$price = sprintf( "%d руб %02d коп", $prc_ru, $prc_kp );
	$aSheet->setCellValue( 'CE164', $price );
	if ( !$ord['paid_ok'] ) $aSheet->setCellValue( 'CE171', $price );

}

// Excel Fill Processing
function docs_xls_fill ( $shit, $line, $start, $word, $plus = 2 ) {

	$len = strlen( $word );
	for ( $l = 0; $l < $len; $l++ ) {
		$shit->setCellValueByColumnAndRow( $start, $line, $word{$l} );
		$start += $plus;
	}

}

/**
 * Возвращает сумму прописью
 * @author runcore
 * @uses morph(...)
 */
function pricenum2str($num) {
    $nul='ноль';
    $ten=array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    $unit=array( // Units
//        array('копейка' ,'копейки' ,'копеек',	 1),
//        array('рубль'   ,'рубля'   ,'рублей'    ,0),
        array('коп' ,'коп' ,'коп',	 1),
        array('руб'   ,'руб'   ,'руб'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );
    //
    list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub)>0) {
        foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
            if (!intval($v)) continue;
            $uk = sizeof($unit)-$uk-1; // unit key
            $gender = $unit[$uk][3];
            list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
            else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
            // units without rub & kop
            if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
        } //foreach
    }
    else $out[] = $nul;
    $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
    $out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
    return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}

/**
 * Склоняем словоформу
 * @ author runcore
 */
function morph($n, $f1, $f2, $f5) {
    $n = abs(intval($n)) % 100;
    if ($n>10 && $n<20) return $f5;
    $n = $n % 10;
    if ($n>1 && $n<5) return $f2;
    if ($n==1) return $f1;
    return $f5;
}

//
// SPSR
//

function docs_spsr_make ( $core, $comp, $from, $to, $onew, $mark, $done ) {
	if ( $from ) {
		$fsql = " AND order_time >= '".strtotime( date2form( $from ) . ' 00:00:00' ). "'";
	} else $fsql = '';

	if ( $to ) {
		$tsql = " AND order_time <= '".strtotime( date2form( $to ) . ' 23:59:59' ). "'";
	} else $tsql = '';

	if ( $onew ) {		$nsql = " AND order_courier = 0 ";
	} else $nsql = '';

	// Load offers and orders
	$offer = $core->db->icol( "SELECT offer_id, offer_descr FROM ".DB_OFFER );
	$order = $core->db->data( "SELECT * FROM ".DB_ORDER." WHERE comp_id = '".$comp['comp_id']."' AND order_delivery = 2 AND order_status IN ( 6, 7 ) $fsql $tsql $usql $nsql ORDER BY order_id ASC LIMIT 1000" );

	// Process order items
	foreach ( $order as &$o ) {		if ( $o['order_items'] && $items = unserialize( $o['order_items'] ) ) {			$vars = $core->wmsale->get( 'vars', $o['offer_id'] );
			if ( count( $items ) == 1 ) {				list( $i, $v ) = each( $items );
                $o['items'] = ( $v > 1 ) ? sprintf( "%s - %s", $vars[$i]['var_short'], $v ) : $vars[$i]['var_short'];
			} else {             	$ib = array();
				foreach ( $items as $i => $v ) $ib[] = sprintf( "%s - %s", $vars[$i]['var_short'], $v );
				$o['items'] = implode( ', ', $ib );
			}
		}
	}

	// Make the listing
	docs_spsr_excel ( $comp, $offer, $order, $core );

	// Marking orders
	foreach ( $order as &$o ) {
		if ( $mark ) $core->db->query( "UPDATE ".DB_ORDER." SET order_courier = 1 WHERE order_id = '".$o['order_id']."' LIMIT 1" );
		if ( $done ) order_edit ( $core, $o['order_id'], array( 'status' => 8 ) );
	}

}

function docs_spsr_excel( $comp, $offer, &$order, $core = null ) {
	// Load the Excel
	require_once PATH_LIB . 'PHPExcel/IOFactory.php';
	$objPHPExcel = PHPExcel_IOFactory::load( PATH_DOCS . "spsr.xls" );
	$objPHPExcel->setActiveSheetIndex(0);
	$aSheet = $objPHPExcel->getActiveSheet();

	// Company info
	$aSheet->setCellValue( 'C4', date( 'd.m.Y' ) );
	$aSheet->setCellValue( 'C5', $comp['comp_spsr'] );
	$aSheet->setCellValue( 'H7', $comp['comp_fio'] );
	$aSheet->setCellValue( 'H8', $comp['comp_addr'] );
	$aSheet->setCellValue( 'H9', $comp['comp_phone'] );

	$i = $addrok = 0;
	foreach ( $order as $o ) {

		// Show the line
    	$i++; $r = 10 + $i;
        $aSheet->getRowDimension( $r )->setVisible( true );

		// Basic order info
		$aSheet->setCellValue( 'A'.$r, $i );
		$aSheet->setCellValue( 'J'.$r, $o['order_name'] );
		$aSheet->setCellValue( 'K'.$r, $o['order_phone'] );
		$aSheet->setCellValue( 'L'.$r, $o['order_id'] );
		$aSheet->setCellValue( 'M'.$r, $o['order_price'] );

		// the Offer
		if ( $o['items'] ) {			$aSheet->setCellValue( 'C'.$r, sprintf( "%s (%s)", $offer[$o['offer_id']], $o['items'] ) );
			$aSheet->setCellValue( 'B'.$r, $o['order_count'] );
		} else {			$aSheet->setCellValue( 'C'.$r, $offer[$o['offer_id']] );
			$aSheet->setCellValue( 'B'.$r, '1' );
		}

		// Order address
		if ( $o['order_addr'] && $o['order_area'] && $o['order_street'] && $o['order_city'] ) {
			$house 	= $o['order_addr'];
			$region	= $o['order_area'];
			$street	= $o['order_street'];
			$city 	= $o['order_city'];

		} else {

			$addr = $o['order_addr'];
			if ( $o['order_street'] ) $addr = $o['order_street'] . ', ' . $addr;
			if ( $o['order_city'] ) $addr = $o['order_city'] . ', ' . $addr;
			if ( $o['order_area'] ) $addr = $o['order_area'] . ', ' . $addr;

			$region = $city = $street = $house = '';
	        $addr = $addr ? parseaddress( $addr ) : false;
			if ( $addr ) {
				foreach ( $addr as $a ) {
		        	if ( $a['id'] == 'zip' ) continue;
		        	if ( $a['id'] == 'street' ) $street = trim( $a['type'], '. ' ) .'. '.$a['value'];
		        	elseif ( $a['id'] == 'city' || $a['id'] == 'place' ) $city .= $a['value'] . ', ';
		        	elseif ( $a['id'] == 'district' ) $city .=  $a['value'] . ' ' .trim( $a['type'], '. ' ).', ';
					elseif ( $a['id'] == 'region' ) {
					    if ( $a['type'] != 'г' ) {
							if ( $a['type'] == 'Респ' ) {
								$region = trim( $a['type'], '. ' ) . '. ' . $a['value'];
							} else $region = $a['value'] . ' ' . $a['type'];
						} else $city = $a['value'] . ', ';
					} else {
						if ( $a['type'] == 'ДОМ' || $a['type'] == 'дом' ) $a['type'] = 'д';
						$house .= trim( $a['type'], '. ' ) . '. ' . $a['value'] . ', ';
					}
				}

				$house = trim ( $house, ', ' );
				$city = trim ( $city, ', ' );
				$addrok += 1;

				if ( $city == 'Москва' && ! $region ) $region = 'Московская обл.';
				if ( $city == 'Санкт-Петербург' && ! $region ) $region = 'Ленинградская обл.';

			}

		}

        // Setting address info
		$aSheet->setCellValue( 'E'.$r, $region );
		$aSheet->setCellValue( 'F'.$r, $city );
		$aSheet->setCellValue( 'G'.$r, $street );
		$aSheet->setCellValue( 'H'.$r, $house );

	}

	// Output the document
	header('Content-type: application/vnd.ms-excel');
	header('Content-Disposition: attachment; filename="spsr.xls"');
	$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel5' );
	$objWriter->save( "php://output" );
	$objPHPExcel->disconnectWorksheets();
	unset ( $aSheet, $objWriter, $objPHPExcel );

	return $addrok;

}