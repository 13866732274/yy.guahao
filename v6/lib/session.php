<?php
/*
// 说明: session 保存到 数据库
// 作者: 幽兰 (weelia@126.com)
// 时间: 2009-10-28 20:00
*/

// 初始化 session:
function ses_open($save_path, $sid)
{
	$db = ses_init_db();

	if (!$db) exit("Error: session can not init with db.");

	//if (mt_rand(1,10) == 1) ses_gc(1440);
	//ses_gc(1440);

	return true;
}

// session 关闭:
function ses_close()
{
	return true;
}

// session 读操作:
function ses_read($sid)
{
	$db = ses_init_db();
	if ($sid) {
		$s = $db->query("select data from sys_session where sid='$sid' limit 1", 1, "data");
		return serialize($s);
	} else {
		return '';
	}

	return true;
}

// session 写操作:
function ses_write($sid, $sess_data)
{
	$db = ses_init_db();

	$data = addslashes($sess_data);
	$time = time();
	$uname = $GLOBALS["realname"];

	if ($sid) {
		if ($db->query("select count(sid) as c from sys_session where sid='$sid' limit 1", 1, "c") > 0) {
			$db->query("update sys_session set uname='$uname', data='$data', updatetime='$time' where sid='$sid' limit 1");
		} else {
			$db->query("insert into sys_session set sid='$sid', uname='$uname', data='$data', addtime='$time', updatetime='$time'");
		}
	}

	return true;
}

// 注销 session:
function ses_destroy($sid)
{
	$db = ses_init_db();

	$db->query("delete from sys_session where sid='$sid' limit 1");

	return true;
}

// gc 压缩:
function ses_gc($maxlifetime)
{
	return false;
}

// gc 压缩: 该功能由系统进行周期性调用
function ses_gc_by_set_env()
{
	$db = ses_init_db();

	$overtime = time() - 180; //超时时间
	$db->query("delete from sys_session where updatetime<$overtime");
	//$db->query("optimize table sys_session"); //因为数据添加删除太频繁，要经常优化表

	return true;
}


// 初始化 db:
function ses_init_db()
{
	if (!$GLOBALS["db"]) {
		// 如果未初始化mysql,尝试初始化之
		$path = dirname(__FILE__) . "/";
		include_once $path . "mysql.php";
		if ($mysql_server) {
			$GLOBALS["mysql_server"] = $mysql_server;
		}
		/* print_r($mysql_server);
		exit; */
		$GLOBALS["db"] = $db = new mysql($mysql_server);
	}

	return $GLOBALS["db"];
}

// 注册 session handler:
session_set_save_handler("ses_open", "ses_close", "ses_read", "ses_write", "ses_destroy", "ses_gc");

// 初始 session:
session_start();