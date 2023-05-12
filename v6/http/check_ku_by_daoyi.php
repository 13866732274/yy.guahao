<?php
// --------------------------------------------------------
// - 功能说明 : 导医添加时 检查资料库重复情况
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2014-5-13
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
	// 读取符合条件的最新的一条记录:
	$line = $db->query("select * from ku_list where hid=$hid and mobile='$tel' order by id desc limit 1", 1);
	if ($line["id"] > 0) {
		$out["status"] = "ku_repeat";

		$r                 = array();
		$r["name"]       = $line["name"];
		$r["sex"]        = $line["sex"];
		$r["age"]        = $line["age"];
		$r["tel"]        = $line["mobile"];
		$r["media_from"] = '网络';

		$content = $line["zx_content"];
		if ($line["qq"] != '') {
			$content .= " QQ:" . $line["qq"];
			}
		if ($line["order_qq"] != '') {
			$content .= " 预约人QQ:" . $line["order_qq"];
			}
		if ($line["weixin"] != '') {
			$content .= " 微信:" . $line["weixin"];
			}
		if ($line["order_weixin"] != '') {
			$content .= " 预约人微信:" . $line["order_weixin"];
			}

		$memo = "从资料库转入";

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