<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			lib / spsr.php
 *  Description:	SPSR tracking module
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

class SPSRtrack {

	private $login;
	private $pass;
	private $id;
	private $cookie;

	public function __construct ( $login, $pass, $id, $cookie ) {
		$this->login	= $login;
		$this->pass		= $pass;
		$this->id		= $id;
		$this->cookie	= sprintf( $cookie, $login );

	}

	private function send ( $url, $post = false ) {

		$curl = curl_init( $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $curl, CURLOPT_COOKIEFILE, $this->cookie );
		curl_setopt( $curl, CURLOPT_COOKIEJAR, $this->cookie );

		if ( $post ) {
			curl_setopt( $curl, CURLOPT_POST, 1 );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $post );
		}

		$result = curl_exec( $curl );
		curl_close( $curl );
		return $result;

	}

	private function request ( $url, $post = false ) {

		$data = $this->send( $url, $post );
		if ( $data ) {        	$data = json_decode( $data, true );
        	if ( ! $data || $data['error'] ) {				return $this->auth() ? json_decode( $this->send( $url, $post ), true ) : false;
        	} else return $data;
		} else return $this->auth() ? json_decode( $this->send( $url, $post ), true ) : false;

	}

	private function auth () {
		$a = $this->send( 'https://cabinet.spsr.ru/php/login.php?Login='.$this->login.'&Pass='.$this->pass );
		if ( $a ) {
			$ai = json_decode( $a, true );
			return $ai['Login']['SID'];
		} else return false;

	}

	public function show ( $date, $from = false ) {

		if ( ! $from ) $from = $date;
		$i2i = array();
		$page = 1;
		while ( true ) {

			$data = $this->request( 'https://cabinet.spsr.ru/php/engine.php?fn=GetExtMon&PageNum='.$page.'&ICN='.$this->id.'&FromDT='.$from.'&ToDT='.$date.'&DeliveryStatus=-1&RowPerPage=500' );
			if ( $data['Invoices']['Invoice'] ) {
				$page += 1;
				foreach ( $data['Invoices']['Invoice'] as $i ) $i2i[$i['GCInvoiceNumber']] = $i['InvoiceNumber'];
			} else break;

		}

		return $i2i;

	}

	public function parcel ( $id ) {
		$data = $this->request( 'https://cabinet.spsr.ru/php/engine.php?fn=InvoiceInfo&PageNum=1&InvoiceNumber='.$id );
		return $data['Invoices']['Invoice'][0] ? $data['Invoices']['Invoice'][0] : false;

	}

	public function price ( $from, $to, $area, $price ) {
		$fd = $this->city ( $from );
		$td = $this->city ( $to, $area );

   		if ( $fd['CityName'] && $td['CityName'] ) {

			$info = $this->request ( 'https://cabinet.spsr.ru/php/engine.php?fn=CalcInvoice&Collect=0&Above35=0&Above80=0&Above200=0&SumDimAbove180=0&IDC=0&InHands=1&Call=1&SMS=0&ICN='.$this->id.'&Pay_Kind=1&City_ID_FR--City_Owner_ID_FRName='.urlencode( $fd['CityName'].', '.$fd['RegionName'] ).'&City_ID_FR--City_Owner_ID_FRRegion='.urlencode( $fd['RegionName'] ).'&City_ID_FR--City_Owner_ID_FR='.$fd['City_ID'].'--'.$fd['City_owner_ID'].'&Country_ID_FR--Country_Owner_ID_FR='.$fd['Country_ID'].'--'.$fd['Country_Owner_ID'].'&City_ID_TO--City_Owner_ID_TOName='.urlencode( $td['CityName'].', '.$td['RegionName'] ).'&City_ID_TO--City_Owner_ID_TORegion='.urlencode( $td['RegionName'] ).'&City_ID_TO--City_Owner_ID_TO='.$td['City_ID'].'--'.$td['City_owner_ID'].'&Country_ID_TO--Country_Owner_ID_TO='.$td['Country_ID'].'--'.$td['Country_Owner_ID'].'&Enclose_Type=16&Weight=0.3&Length=0&Width=0&Depth=0&InsuranceCost=0&DeclaredCost=' . $price );

			return $info['main_services'][0]['delivery_mode'] ? $info['main_services'][0] : null;

		} else return false;

	}

	private function city ( $name, $area = false ) {
		if ( strpos( $name, ',' ) !== false ) {			$name = explode( ',', $name );
			$name = trim ( $name[1] );
		}

		if ( defined( 'SPSR_CACHE' ) ) {			$cachename = sprintf( SPSR_CACHE, md5( $name . $area ) );
		} else $cachename = false;

		if ( $cachename && file_exists( $cachename ) ) return unserialize(file_get_contents( $cachename ));

		$info = json_decode( file_get_contents ( 'https://cabinet.spsr.ru/php/engine.php?fn=GetCities&CityName='.urlencode( $name ).'&CountryName=%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D1%8F' ), true );
		if ( ! $info['City']['Cities'][0] ) {			if ( $area ) {				$info = json_decode( file_get_contents ( 'https://cabinet.spsr.ru/php/engine.php?fn=GetCities&CityName='.urlencode( $name . ', ' . $area ).'&CountryName=%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D1%8F' ), true );
				if ( $info['City']['Cities'][0] ) {                	 $data = $info['City']['Cities'][0];
				} else $data = false;
			} else $data = false;
		} else $data = $info['City']['Cities'][0];

		if ( $cachename ) file_put_contents( $cachename, serialize( $data ) );
		return $data;

	}

	public static function info ( $code ) {

		$data = simplexml_load_file( 'http://www.spsr.ru/sites/default/modules/spsr/publicapi/index.php?xml=%3C?xml%20version=%221.0%22?%3E%3Croot%20xmlns=%22http://spsr.ru/webapi/Monitoring/MonInvoiceInfo/1.0%22%3E%3Cp:Params%20Name=%22WAMonitorInvoiceInfo%22%20Ver=%221.1%22%20xmlns:p=%22http://spsr.ru/webapi/WA/1.0%22/%3E%3CLogin/%3E%3CMonInvoiceInfo%20InvoiceNumOrBCorGC=%22'.$code.'%22%20Language=%22ru%22/%3E%3C/root%3E' );

		if ( $data ) {

			$info = array();
			foreach ( $data->event->value as $v ) {
				$dd = explode( 'T', (string) $v['Date'] );
				$dt = explode( '-', $dd[0] );
				$tt = explode( '.', $dd[1] );
				$info[] = array(
					'date'		=> sprintf( "%02d.%02d.%04d", $dt[2], $dt[1], $dt[0] ),
					'time'		=> $tt[0],
					'status'	=> (string) $v['EventName'],
					'city'		=> (string) $v['City'],
					'code'		=> (int) $v['EventCode'],
					'info'		=> trim( (string) $v['EventStrCode'] ),
				);
			}
			return $info;

		} else return false;

	}

	public static function check ( $code ) {

		$info = SPSRtrack::info( $code );
		if ( $info ) {
			$position = end( $info );
			return array( $position['date'], $position['status'], $position['info'] );
		} else return false;

	}

}