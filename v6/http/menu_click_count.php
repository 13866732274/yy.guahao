<?php
// --------------------------------------------------------
// - 功能说明 : 记录用户菜单的点击次数
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-06-14 10:45
// --------------------------------------------------------
require "../lib/set_env.php";
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

//username的检查:
if ($username == "") exit;

//$_GET["mid"] = 32;
//$uinfo["menuclicks"] = "44:2;32:4;65:1";

//参数获取:
$mid = intval($_GET["mid"]);
if ($mid == 0) exit;

//解析原数据:
$mm = array();
if ($mc = trim($uinfo["menuclicks"])) {
	$mcs = explode(";", $mc);
	foreach ($mcs as $mci) {
		list($mcid, $mcclick) = explode(":", $mci);
		if ($mcid > 0) {
			$mm[$mcid] = $mcclick;
		}
	}
}

//加入本次计数:
$mm[$mid] = intval($mm[$mid]) + 1;

//倒序排序
arsort($mm);

//合并:
$out = array();
$mmcount = 0;
foreach ($mm as $k => $v) {
	if ($v > 0) {
		$out[] = $k.":".$v;
		if (++$mmcount >= 20) break; //仅保留前20个
	}
}
$menuclicks = implode(";", $out);

echo $menuclicks;

//保存:
$db->query("update sys_admin set menuclicks='$menuclicks' where name='$username' limit 1");

exit("1");
?>