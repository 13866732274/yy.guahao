<?php
// --------------------------------------------------------
// - 功能说明 : online talk save messages
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-05-18 23:12
// --------------------------------------------------------
require "../lib/set_env.php";
require "../lib/class.fastjson.php";
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

$nLastCheckTime = strtotime($_GET["t"]);

$timestamp = time();

$sqlwhere = $nLastCheckTime > 0 ? " and t.addtime>$nLastCheckTime" : "";
$data = $db->query("select t.id, t.fromname, u.realname, t.addtime, t.content, t.link from sys_message t left join sys_admin u on t.fromname=u.name where t.toname='$username' and t.readtime=0 $sqlwhere order by t.addtime desc limit 8");

foreach ($data as $line) {
	$messid = $line["id"];
	$aMessInfo[$messid] = array(
		"fromname"=>$line["fromname"],
		"realname"=>$line["realname"],
		"time"=>date("Y-m-d H:i:s", $line["addtime"]), "content"=>face_show(text_show($line["content"])),
		"link" => $line["link"] ? $line["link"] : "javascript:void(0);",
	);
	$db->query("update sys_message set readtime=$timestamp where id=$messid limit 1");
}

echo FastJSON::convert($aMessInfo);

?>