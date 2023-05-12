<?php
// --------------------------------------------------------
// - 功能说明 : 检查数据重复情况 (资料库)
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2014-4-21
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
		$alert = '“'.$tel.'”已经被“'.$line["u_name"]."”在 ".date("Y-m-d H:i", $line["addtime"])." 添加到：".$line["h_name"]."，请酌情考虑是否继续添加！";
		$tips = "<font color=red>无效</font>　";
	} else {
		$tips = "有效　";
	}
}

$out["status"] = "ok";
$out["alert"] = $alert;
$out["tips"] = $tips;

echo FastJSON::convert($out);
?>