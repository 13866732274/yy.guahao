<?php
// --------------------------------------------------------
// - ����˵�� : ��ҽ���ʱ ������Ͽ��ظ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2014-5-13
// --------------------------------------------------------
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
require "../lib/set_env.php";
require "../lib/class.fastjson.php";

$out             = array();
$out["status"] = "ok";

$tel = trim($_REQUEST["tel"]);
if ($tel >= 11) {
	// ��ȡ�������������µ�һ����¼:
	$line = $db->query("select * from ku_list where hid=$hid and mobile='$tel' order by id desc limit 1", 1);
	if ($line["id"] > 0) {
		$out["status"] = "ku_repeat";

		$r                 = array();
		$r["name"]       = $line["name"];
		$r["sex"]        = $line["sex"];
		$r["age"]        = $line["age"];
		$r["tel"]        = $line["mobile"];
		$r["media_from"] = '����';

		$content = $line["zx_content"];
		if ($line["qq"] != '') {
			$content .= " QQ:" . $line["qq"];
			}
		if ($line["order_qq"] != '') {
			$content .= " ԤԼ��QQ:" . $line["order_qq"];
			}
		if ($line["weixin"] != '') {
			$content .= " ΢��:" . $line["weixin"];
			}
		if ($line["order_weixin"] != '') {
			$content .= " ԤԼ��΢��:" . $line["order_weixin"];
			}

		$memo = "�����Ͽ�ת��";

		$r["content"] = $content;
		$r["memo"]    = $memo;
		$r["from"]    = "ku";
		$r["ku_id"]   = $line["id"];

		$r["from_part_id"] = $line["part_id"];
		$r["from_uid"]     = $line["uid"];
		$r["from_author"]  = $line["u_name"];

		foreach ($r as $k => $v) {
			$r[$k] = $k . "=" . urlencode($v);
			}

		$link = "patient_add.php?op=add&" . implode("&", $r);

		$out["url"] = $link;
		}
	}

echo FastJSON::convert($out);