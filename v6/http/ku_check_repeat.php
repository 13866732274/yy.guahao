<?php
// --------------------------------------------------------
// - ����˵�� : ��������ظ���� (���Ͽ�)
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2014-4-21
// --------------------------------------------------------
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
require "../lib/set_env.php";
require "../lib/class.fastjson.php";

$tel = trim($_GET["tel"]);
$alert = $tips = "";
if ($tel != '') {
	$line = $db->query("select * from ku_list where mobile='$tel' order by id desc limit 1", 1);
	if ($line["id"] > 0) {
		$alert = '��'.$tel.'���Ѿ�����'.$line["u_name"]."���� ".date("Y-m-d H:i", $line["addtime"])." ��ӵ���".$line["h_name"]."�������鿼���Ƿ������ӣ�";
		$tips = "<font color=red>��Ч</font>��";
	} else {
		$tips = "��Ч��";
	}
}

$out["status"] = "ok";
$out["alert"] = $alert;
$out["tips"] = $tips;

echo FastJSON::convert($out);
?>