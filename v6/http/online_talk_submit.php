<?php
// --------------------------------------------------------
// - 功能说明 : online talk save messages
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2007-08-19 22:40
// --------------------------------------------------------
require "../lib/set_env.php";

$timestamp = time();
$fromname = $username;

$replyfromid = intval($_POST["messid"]);
$toname = convert($_POST["name"], "utf-8", "gb2312");
$content = convert($_POST["content"], "utf-8", "gb2312");

if ($db->query("insert into sys_message set fromname='$fromname', toname='$toname', replyfromid='$replyfromid', content='$content', addtime='$timestamp'")) {
	echo $replyfromid;
}
?>