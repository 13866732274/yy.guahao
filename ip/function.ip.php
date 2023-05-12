<?php
// --------------------------------------------------------
// - 功能说明 : 查询ip所在的地区
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-08-20 14:14
// --------------------------------------------------------
function get_city()
{
	$theArea = ip_area(_get_ip(), 1);

	if (strpos($theArea, "省") > 0) {
		list($theProvince, $theCity) = explode("省", $theArea, 2);
	} else {
		$theCity = $theArea;
	}

	if (strpos($theCity, "市") > 0) {
		list($theCity, $other) = explode("市", $theCity, 2);
	} else {
		$theCity = "";
	}

	return $theCity;
}

// 获取访问者的IP地址，优先使用真实IP检测（代理的最底层，但不是局域网内）
// 无法得到则说明未使用代理，则直接使用IP地址。
function _get_ip()
{
	$long_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	if ($long_ip != "") {
		foreach (explode(",", $long_ip) as $cur_ip) {
			list($ip1, $ip2) = explode(".", $cur_ip, 2);
			if ($ip1 <> "10") {
				return $cur_ip;
			}
		}
	}
	return $_SERVER["REMOTE_ADDR"];
}

// 此函数用于精确计时
function _get_time()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

// 获取IP所对应的地理位置
function ip_area($ip, $type = 0)
{
	if (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
		return false;
	}

	$ipfile = dirname(__FILE__) . "/ip_area.dat";

	//if (!file_exists($ipfile)) {
	//	echo $ipfile.",ip data file not exists...";
	//	return false;
	//}

	if (!$fd = @fopen($ipfile, 'rb')) {
		echo "can not open ip data file...";
		return false;
	}

	$ip    = explode('.', $ip);
	$ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

	$DataBegin = fread($fd, 4);
	$DataEnd   = fread($fd, 4);
	$ipbegin   = implode('', unpack('L', $DataBegin));
	if ($ipbegin < 0) $ipbegin += pow(2, 32);
	$ipend = implode('', unpack('L', $DataEnd));
	if ($ipend < 0) $ipend += pow(2, 32);
	$ipAllNum = ($ipend - $ipbegin) / 7 + 1;

	$BeginNum = 0;
	$EndNum   = $ipAllNum;

	while ($ip1num > $ipNum || $ip2num < $ipNum) {
		$Middle = intval(($EndNum + $BeginNum) / 2);

		fseek($fd, $ipbegin + 7 * $Middle);
		$ipData1 = fread($fd, 4);
		if (strlen($ipData1) < 4) {
			fclose($fd);
			return 'system error';
		}
		$ip1num = implode('', unpack('L', $ipData1));
		if ($ip1num < 0) $ip1num += pow(2, 32);

		if ($ip1num > $ipNum) {
			$EndNum = $Middle;
			continue;
		}

		$DataSeek = fread($fd, 3);
		if (strlen($DataSeek) < 3) {
			fclose($fd);
			return 'system error';
		}
		$DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
		fseek($fd, $DataSeek);
		$ipData2 = fread($fd, 4);
		if (strlen($ipData2) < 4) {
			fclose($fd);
			return 'system error';
		}
		$ip2num = implode('', unpack('L', $ipData2));
		if ($ip2num < 0) $ip2num += pow(2, 32);

		if ($ip2num < $ipNum) {
			if ($Middle == $BeginNum) {
				fclose($fd);
				return '未知ip...';
			}
			$BeginNum = $Middle;
		}
	}

	$ipFlag = fread($fd, 1);
	if ($ipFlag == chr(1)) {
		$ipSeek = fread($fd, 3);
		if (strlen($ipSeek) < 3) {
			fclose($fd);
			return 'system error';
		}
		$ipSeek = implode('', unpack('L', $ipSeek . chr(0)));
		fseek($fd, $ipSeek);
		$ipFlag = fread($fd, 1);
	}

	if ($ipFlag == chr(2)) {
		$AddrSeek = fread($fd, 3);
		if (strlen($AddrSeek) < 3) {
			fclose($fd);
			return 'System Error';
		}
		$ipFlag = fread($fd, 1);
		if ($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if (strlen($AddrSeek2) < 3) {
				fclose($fd);
				return 'system error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}

		while (($char = fread($fd, 1)) != chr(0))
			$ipAddr2 .= $char;

		$AddrSeek = implode('', unpack('L', $AddrSeek . chr(0)));
		fseek($fd, $AddrSeek);

		while (($char = fread($fd, 1)) != chr(0))
			$ipAddr1 .= $char;
	} else {
		fseek($fd, -1, SEEK_CUR);
		while (($char = fread($fd, 1)) != chr(0))
			$ipAddr1 .= $char;

		$ipFlag = fread($fd, 1);
		if ($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if (strlen($AddrSeek2) < 3) {
				fclose($fd);
				return 'system error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}
		while (($char = fread($fd, 1)) != chr(0))
			$ipAddr2 .= $char;
	}
	fclose($fd);

	if (preg_match('/http/i', $ipAddr2)) {
		$ipAddr2 = '';
	}
	$ipaddr = "$ipAddr1 $ipAddr2";
	$ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
	$ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
	$ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
	if (preg_match('/http/i', $ipaddr) || $ipaddr == '') {
		$ipaddr = '未知ip...';
	}

	return $ipaddr;
}