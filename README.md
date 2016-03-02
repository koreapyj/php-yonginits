# php-yonginits
용인시 ITS에서 정보를 캐옵니다. 그런데 이제 GBIS에서 용인시 마을버스 확인이 가능한데 이걸 어디에 쓸까요?

## 라이선스
GNU Affero GPL 3.0에 따라 자유롭게 사용하실 수 있습니다. 라이선스에 대한 자세한 사항은 첨부 문서를 참고하십시오.

## 쓰는 법
원래 이렇게 쓰라고 만든게 아니다보니 쓰는 방법이 좀 난해합니다.

    // 라이브러리를 불러옵니다.
    require_once('./inc/yonginits.php');
    
    /* 
     * YonginITS YonginITS::_construct(bool $cache=false, string $cache_dir=null, string[] $cache_enabled_method=[1,2,3,4,5], str $base="http://its.yonginsi.net")
     * 인수: 파일 캐시 사용 여부, 파일 캐시 저장 폴더, 캐시를 사용할 메서드, 도메인
     * 라이브러리를 초기화합니다.
     * 
     * 주의: 캐시를 사용하지 않으면 매우 느리거나 용인시가 당신의 IP를 차단할 것입니다.
     */
    $_its = new YonginITS(true, "./.its_cache", [1,2,3,4,5]);
  
    /* 
     * void YonginITS::purgeCache()
     * 인수: 없음
     * 파일 캐시를 초기화합니다. 원래 잘 되던게 갑자기 안 될때 한번씩 실행시키면 좋습니다.
     */
    $_its->purgeCache();
  
    /* 
     * object YonginITS::getBusInfo(int $method = 2, string $itsid = null)
     * 인수: 메서드, 노선ID
     * 버스 정보를 가져옵니다.
     * GBIS와는 달리 정류소 정보, 차량 정보, 노선 경로가 통합되어 있습니다.
     * 메서드:
     *  1: 불명.
     *  2: 기종점 정보, 버스회사 정보를 가져옴. $itsid에 노선ID 필요.
     *  3: 첫막차시간과 배차간격 정보를 가져옴. $itsid에 노선ID 필요. 요즘 뭐 망가져서 잘 안됨.
     *  4: 정류소 목록을 가져옴. $itsid에 노선ID 필요.
     *  5: 노선 경로를 가져옴. 좌표 형식은 EPSG:5174. $itsid에 노선ID 필요.
     *  6: 운행중인 차량 목록을 가져옴. $itsid에 노선ID 필요. 위치는 좌표로만 나옴. 하지만 반드시 정류소 좌표와 동일하게 나오므로 정류소 정보에서 찾으면 ok.
     *  7: ITS에 입력되어 있는 모든 정류소 목록을 가져옴. $itsid는 null. 정류소ID는 GBIS와 공용인듯.
     */
    print_r($_its->getBusInfo(2, '41420001'));
    print_r($_its->getBusInfo(3, '41420001'));
    print_r($_its->getBusInfo(4, '41420001'));
    print_r($_its->getBusInfo(5, '41420001'));
    print_r($_its->getBusInfo(6, '41420001'));

### 출력
출력값은 나도 설명하기 귀찮으니 눈치껏 쓰시기 바랍니다.

	SimpleXMLElement Object
	(
	    [BusRoute] => SimpleXMLElement Object
		(
		    [id] => 41420001
		    [name] => 1
		    [type] => 30
		    [typeName] => 마을버스
		    [stStationID] => 203000316
		    [stStationName] => 광교웰빙타운.열림공원
		    [edStationID] => 243501390
		    [edStationName] => 죽전역
		    [length] => 18660
		    [turnSeq] => 28
		    [companyID] => null
		    [companyName] => null
		    [phone] => null
		    [addr] => null
		    [interval] => 0
		)

	)
	SimpleXMLElement Object
	(
	    [0] => 

	)
	SimpleXMLElement Object
	(
	    [BusRoute] => Array
		(
		    [0] => SimpleXMLElement Object
			(
			    [id] => 203000316
			    [name] => 광교웨빙타운.열림공원
			    [order] => 1
			    [type] => 4
			    [typeName] => 일반형시외버스
			    [centerFlag] => N
			    [x] => 127.038727
			    [y] => 37.3080864
			    [mobileNo] => null
			)
			**(중략)**
		    [54] => SimpleXMLElement Object
			(
			    [id] => 203000315
			    [name] => 광교웨빙타운.열림공원
			    [order] => 55
			    [type] => 4
			    [typeName] => 일반형시외버스
			    [centerFlag] => N
			    [x] => 127.038951
			    [y] => 37.3081603
			    [mobileNo] => null
			)

		)

	)
	SimpleXMLElement Object
	(
	    [BusRoute] => Array
		(
		    [0] => SimpleXMLElement Object
			(
			    [routeUnitSeq] => 1
			    [coord] => 20336432.11,42289966.73
			    [updown] => 0
			)
			**(중략)**
		    [394] => SimpleXMLElement Object
			(
			    [routeUnitSeq] => 100
			    [coord] => 20338623.58,42290789.9
			    [updown] => 1
			)

		)

	)
	SimpleXMLElement Object
	(
	    [BusRoute] => Array
		(
		    [0] => SimpleXMLElement Object
			(
			    [vehID] => 141789124
			    [plateNo] => 경기78아9124
			    [x] => 127.05186
			    [y] => 37.30589
			    [updown] => 0
			)
			**(중략)**
		    [8] => SimpleXMLElement Object
			(
			    [vehID] => 141788854
			    [plateNo] => 경기78아8854
			    [x] => 127.044593811
			    [y] => 37.3054771423
			    [updown] => 1
			)

		)

	)

## 팁
쓰면서 어려웠던 부분 몇 가지를 소개합니다.

### EPSG:5174 좌표 변환 방법
Proj4에 이걸 넣으시면 될 것 같습니다.

	+proj=tmerc +lat_0=38 +lon_0=127.0028902777778 +k=1 +x_0=200000 +y_0=500000 +ellps=bessel +units=m +no_defs +towgs84=-115.80,474.99,674.11,1.16,-2.31,-1.63,6.43
	
다음 지도 API 쓰실거면 "TM"좌표계를 고르세요.
