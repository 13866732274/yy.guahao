<?php
// --------------------------------------------------------
// - ����˵�� : online talk save messages
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2007-08-19 22:40
// --------------------------------------------------------
require "../lib/set_env.php";

$timestamp = time();

$nMessID = intval($_GET["id"]);
if ($db->query("update sys_message set readtime='$timestamp' where id='$nMessID' limit 1")) {
	echo $nMessID;
}
?>