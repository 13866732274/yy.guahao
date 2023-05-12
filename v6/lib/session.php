<?php
/*
// ˵��: session ���浽 ���ݿ�
// ����: ���� (weelia@126.com)
// ʱ��: 2009-10-28 20:00
*/

// ��ʼ�� session:
function ses_open($save_path, $sid)
{
	$db = ses_init_db();

	if (!$db) exit("Error: session can not init with db.");

	//if (mt_rand(1,10) == 1) ses_gc(1440);
	//ses_gc(1440);

	return true;
}

// session �ر�:
function ses_close()
{
	return true;
}

// session ������:
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

// session д����:
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

// ע�� session:
function ses_destroy($sid)
{
	$db = ses_init_db();

	$db->query("delete from sys_session where sid='$sid' limit 1");

	return true;
}

// gc ѹ��:
function ses_gc($maxlifetime)
{
	return false;
}

// gc ѹ��: �ù�����ϵͳ���������Ե���
function ses_gc_by_set_env()
{
	$db = ses_init_db();

	$overtime = time() - 180; //��ʱʱ��
	$db->query("delete from sys_session where updatetime<$overtime");
	//$db->query("optimize table sys_session"); //��Ϊ�������ɾ��̫Ƶ����Ҫ�����Ż���

	return true;
}


// ��ʼ�� db:
function ses_init_db()
{
	if (!$GLOBALS["db"]) {
		// ���δ��ʼ��mysql,���Գ�ʼ��֮
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

// ע�� session handler:
session_set_save_handler("ses_open", "ses_close", "ses_read", "ses_write", "ses_destroy", "ses_gc");

// ��ʼ session:
session_start();