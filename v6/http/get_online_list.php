<?php
// --------------------------------------------------------
// - 功能说明 : get_online.php
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2007-08-19 21:00
// --------------------------------------------------------
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
require "../lib/set_env.php";
require "../lib/class.fastjson.php";

$timestamp = time();

// 保存当前用户本次在线的记录:
$db->query("update sys_admin set lastactiontime='$timestamp',online=1 where name='$username' limit 1");

// 更新用户在线状态:
$db->query("update sys_admin set online=if($timestamp-lastactiontime>60, '0', '1') where online=1");

// 读取在线用户:
$aUser = array();
$aUser[$username] = array("realname"=>$realname, "isowner"=>1);

$sqlwhere = '';
if ($user_hospital_id > 0) {
	$sqlwhere = "and concat(',',hospitals,',') like '%,{$user_hospital_id},%'";
}

$data = $db->query("select name,realname,hospitals from sys_admin where name!='$username' and online='1' $sqlwhere order by thislogin desc limit 10");

foreach ($data as $line) {
	// 2009-05-09 16:55 修改：读取同一医院的在线用户
	//if (!$user_hospital_id || in_array($user_hospital_id, explode(',', $line["hospitals"]))) {
		$aUser[$line["name"]] = array("realname"=>$line["realname"], "isowner"=>0);
	//}
}

echo FastJSON::convert($aUser);
?>