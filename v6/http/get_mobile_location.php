<?php
// --------------------------------------------------------
// - ����˵�� : ��ѯ���������
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2010-11-15 12:29
// --------------------------------------------------------
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
require "../lib/set_env.php";
require "../lib/class.fastjson.php";

$out = array();
$out["status"] = "bad";

$m = $_GET["m"];

// �ֻ����������11λ���ܲ�ѯ
if (strlen($m) != 11) {
	echo FastJSON::convert($out);
	exit;
}


/*
// ��ѯ�ӿ�:
$sm = substr($m, 0, 8);
$c = (string) @file_get_contents("http://www.ip138.com:8080/search.asp?action=mobile&mobile=".$sm);

// ���˴����еĻس������Ʊ��(������һ����������ƥ��):
$c = str_replace("\t", "", str_replace("\n", "", str_replace("\r", "", $c)));

// ǰ���ַ���:
$pre = '<TD width="130" align="center" noswap>���Ź�����</TD><TD width=* align="center" class=tdc2>';
$end = '</TD>';

// ��������ģʽƥ��:
preg_match_all("/".preg_quote($pre, '/')."(.*?)".preg_quote($end, '/')."/i", $c, $rs);

if ($_GET["s"] == 1) {
	echo "<pre>";
	print_r($rs);
	exit;
}

$lo = '';
if ($rs[1][0]) {
	$lo = str_replace("&nbsp;", " ", $rs[1][0]);

	// һЩ����:
	$lo = trim($lo);
	$lo = str_replace("ʡ", "", $lo);
	$lo = str_replace("��", "", $lo);

	// ������Ϻ� �Ϻ�������� ��� ����������
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