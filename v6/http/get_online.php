<?php
/*
// - 功能说明 : get_online.php
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-05-26 11:53
*/
ob_start();
require "../lib/set_env.php";
require "../lib/class.fastjson.php";
ob_end_clean();

ob_start();
error_reporting(0);
set_time_limit(5);

$out = array();
$out["status"] = "bad";

$time = time();

// 保存当前用户本次在线的记录:
$db->query("update sys_admin set lastactiontime='$time',online=1 where name='$username' limit 1");

// 更新用户在线状态:
$last_update_time = @intval(file_get_contents("get_online_time.txt"));
if (time() - $last_update_time >= 60) {
	@file_put_contents("get_online_time.txt", time());
	$db->query("update sys_admin set online=if($time-lastactiontime>90, '0', '1') where online=1");
}

// 读取在线用户:
$aUser = array();
$aUser[$username] = array("realname"=>$realname, "isowner"=>1);

$out["online_list"] = $aUser;
$out["online_message"] = array();
$out["status"] = "ok";

// 增加抓取手机号提醒功能
$t = strtotime("-3 days");
$mo_num = $db->query("select count(*) as c from mobile_catch where kefu_uid=$uid and huifang='' and addtime>$t", 1, "c");
if ($debug_mode) {
	//$mo_num = 5;
}
$out["mo_catch_num"] = $mo_num;

ob_end_clean();

echo 'var _online_data = '.FastJSON::convert($out)."\n";
echo 'get_online_do(_online_data);'."\n";
?>