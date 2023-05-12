<?php
// --------------------------------------------------------
// - 功能说明 : 查询号码归属地
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2010-11-15 12:29
// --------------------------------------------------------
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
require "../lib/set_env.php";
require "../lib/class.fastjson.php";

$out = array();
$out["status"] = "bad";

$m = $_GET["m"];

// 手机号码必须是11位才能查询
if (strlen($m) != 11) {
	echo FastJSON::convert($out);
	exit;
}


/*
// 查询接口:
$sm = substr($m, 0, 8);
$c = (string) @file_get_contents("http://www.ip138.com:8080/search.asp?action=mobile&mobile=".$sm);

// 过滤代码中的回车换行制表符(便于下一步进行正则匹配):
$c = str_replace("\t", "", str_replace("\n", "", str_replace("\r", "", $c)));

// 前后字符串:
$pre = '<TD width="130" align="center" noswap>卡号归属地</TD><TD width=* align="center" class=tdc2>';
$end = '</TD>';

// 进行正则模式匹配:
preg_match_all("/".preg_quote($pre, '/')."(.*?)".preg_quote($end, '/')."/i", $c, $rs);

if ($_GET["s"] == 1) {
	echo "<pre>";
	print_r($rs);
	exit;
}

$lo = '';
if ($rs[1][0]) {
	$lo = str_replace("&nbsp;", " ", $rs[1][0]);

	// 一些修正:
	$lo = trim($lo);
	$lo = str_replace("省", "", $lo);
	$lo = str_replace("市", "", $lo);

	// 解决“上海 上海”“天津 天津” 这样的问题
	$ab = explode(" ", $lo);
	if (count($ab) == 2 && $ab[0] == $ab[1]) {
		$lo = $ab[0];
	}
}
*/

$lo = (string) @get_mobile_location($m);

$out["status"] = "ok";
$out["m"] = $m;
$out["location"] = $lo;

echo FastJSON::convert($out);
?>