<?php
/**
 * yonginits.php - Namu Mark Renderer
 * Copyright (C) 2016 koreapyj koreapyj0@gmail.com
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */
 
class YonginITS {
	private $base;

	function __construct($cache=false, $cache_dir=null, $cache_enabled_method = array(1,2,3,4,5), $base = 'http://its.yonginsi.net') {
		$this->base = $base;
		$this->cache = $cache;
		$this->cache_dir = $cache_dir!==null?rtrim($cache_dir, '/').'/':null;
		$this->cache_enabled_method = $cache_enabled_method;

		if($this->cache)
			$this->initCacheDir();
	}

	function initCacheDir() {
		if(!$this->cache)
			return;

		if(!is_dir($this->cache_dir))
			mkdir($this->cache_dir);
	}

	function purgeCache() {
		if(!$this->cache)
			return;

		if(is_dir($this->cache_dir))
			system('rm -rf '.$this->cache_dir);

		$this->initCacheDir();
	}

	function putCache($name, $value) {
		if(!$this->cache)
			return;
		$this->initCacheDir();

		file_put_contents($this->cache_dir.$this->encodeCacheName($name), $value);
		return;
	}

	function getCache($name) {
		if(!$this->cache)
			return false;
		return file_exists($this->cache_dir.$this->encodeCacheName($name))
						?file_get_contents($this->cache_dir.$this->encodeCacheName($name))
						:false
			;
	}

	function encodeCacheName($str) {
		return str_replace('%', '_', rawurlencode($str));
	}

	function decodeCacheName($str) {
		return rawurldecode(str_replace('_', '%', $str));
	}

	function getObjectByCoord($haystack, $needleX, $needleY) {
		if(!isset($haystack[0]))
			return false;

		$i=0;
		foreach($haystack as $stop) {
			if($stop->x.' ' == $needleX.' ' && $stop->y.' ' == $needleY.' ') {
				return $i;
			}
			$i++;
		}
		return false;
	}

	function getBusInfo($method = 2, $itsid = null) {
		$cacheName = 'busInfo-'.$method.(!is_null($itsid)?'-'.$itsid:'').'.xml';
		
		if(!in_array($method, $this->cache_enabled_method) || !$body = $this->getCache($cacheName)) {
			$rqBody = 'method='.$method.'&busRouteID='.$itsid;
			$opts = array(
				'http'=>array(
					'method'=>"POST",
					'header'=>'Host: '.parse_url($this->base, PHP_URL_HOST)."\r\n".
										'Referer: '.$this->base.'/busInfo.do'."\r\n".
										'Cache-Control: no-cache'."\r\n".
										'Content-Type: application/x-www-form-urlencoded'."\r\n".
										'Content-Length: '.strlen($rqBody)."\r\n".
										'Connection: close'."\r\n".
										'',
					'user_agent'=>'Mozilla/5.0 (compatible; YonginITS/0.1)',
					'content'=>$rqBody,
					'timeout' => 5
				)
			);
			$context = stream_context_create($opts);
			if(!$body = @file_get_contents($this->base.'/BusServlet.do', false, $context))
				return false;
			$this->putCache($cacheName, $body);
		}
		return $this->getXmlObject($body);
	}

	function getStationInfo($itsid = null) {
		$opts = array(
			'http'=>array(
				'method'=>"GET",
				'header'=>'Host: '.parse_url($this->base, PHP_URL_HOST)."\r\n".
									'Referer: '.$this->base.'/busInfo.do'."\r\n".
									'Cache-Control: no-cache'."\r\n".
									'Connection: close'."\r\n".
									'',
				'user_agent'=>'Mozilla/5.0 (compatible; YonginITS/0.1)',
				'timeout' => 5
			)
		);
		$context = stream_context_create($opts);
		if(!$body = @file_get_contents($this->base.'/busInfoPopup.do?stationId='.$itsid, false, $context))
			return false;
		if(!preg_match_all('/<tbody class="bus">(.*)<\/tbody>/sU', $body, $station) || !(isset($station[1][0]) && ($arrivalbody = $station[1][0])?true:false) || !(isset($station[1][1]) && ($routebody = $station[1][1])?true:false))
			return false;
		if(!preg_match_all('/<tr>(.*)<\/tr>/sU', $arrivalbody, $arrivallist) || !(isset($arrivallist[1]) && ($arrivallist = $arrivallist[1])?true:false))
			return false;
		if(!preg_match_all('/<tr>(.*)<\/tr>/sU', $routebody, $routelist) || !(isset($routelist[1]) && ($routelist = $routelist[1])?true:false))
			return false;

		$outbuf = [];
		$outbuf['ArrivalList'] = [];
		foreach($arrivallist as $key => $arrival) {
			if(!preg_match_all('/<td>(.*)<\/td>/sU', $arrival, $arrival_info))
				continue;
			$arrival_info[1][1] = preg_replace(array('/^([0-9]+)번째 전.*$/', '/^잠시후 도착$/'), array('$1', '1'), $arrival_info[1][1]);
			$arrival_info[1][2] = preg_replace(array('/^약 ([0-9]+)분.*$/', '/^잠시후 도착$/'), array('$1', '0'), $arrival_info[1][2]);
			$outbuf['ArrivalList'][$arrival_info[1][0]] = $arrival_info[1];
		}
		$outbuf['RouteList'] = [];
		foreach($routelist as $key => $route) {
			if(!preg_match_all('/<td>(.*)<\/td>/sU', $route, $route_info))
				continue;
			$outbuf['RouteList'][$route_info[1][0]] = $route_info[1];
		}
		return $outbuf;
	}

	function getXmlObject($str) {
		return simplexml_load_string($str, 'SimpleXMLElement', LIBXML_NOCDATA);
	}
}
