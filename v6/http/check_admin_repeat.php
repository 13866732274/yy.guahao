<?php
// --------------------------------------------------------
// - ����˵�� : ��������ظ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-07-26
// --------------------------------------------------------
header("Content-Type:text/javascript;charset=gbk");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
require "../lib/set_env.php";

$name = mb_convert_encoding($_GET["js_name"], "gbk", "UTF-8");

$tips = "";
if ($name != "") {
	$line = $db->query("select * from sys_admin where name='$name' or realname='$name' limit 1", 1);

	$tips = "���á�";
	if ($line["id"] > 0) {
		$tips = "��" . $name . "�������ظ������޸ġ�";
		}
	}

echo "byid('name_tips').innerHTML = '{$tips}';";