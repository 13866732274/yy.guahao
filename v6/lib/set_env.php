<?php
// --------------------------------------------------------
// - ����˵�� : set_env.php
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2007-01-01 20:00
// --------------------------------------------------------
error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(15);
ini_set("display_errors", "On");
ini_set("log_errors", 0);

define("ROOT", dirname(dirname(dirname(__FILE__))) . "/");
define("UPFILE_DIR", ROOT . "upfile/");

require_once ROOT . "./v6/lib/config.php";

$host_name = $_SERVER["HTTP_HOST"];

function now()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

$pagebegintime = now();

$maxlifetime = 14400;
if (@ini_get("session.gc_maxlifetime") < $maxlifetime) {
	//@ini_set("session.gc_maxlifetime", $maxlifetime);
}
$time = $timestamp = time(); //��ǰʱ�䣬���������еط�ʹ��

$query_count = 0;


// ���ĺ�����
require_once ROOT . "./v6/lib/function.php";
require_once ROOT . "./v6/lib/function.mem_cache.php";

// mysql ��Ⲣ��ʼ��
require_once ROOT . "./v6/lib/mysql.php";
$db = new mysql();

// session ���ƺ���
require_once ROOT . "./v6/lib/session.php";


$uid = intval($_SESSION[$cfgSessionName]["uid"]);
$username = $_SESSION[$cfgSessionName]["username"];
$realname = $_SESSION[$cfgSessionName]["realname"];
$debug_mode = $_SESSION[$cfgSessionName]["debug"] ? 1 : 0;

// ~~~~~~~~~~ sessionʧЧ�����������ύʧ�ܵĴ���:
if ($uid == 0) {
	if ($_POST) {
		$tmp = "<table width='80%' align='center'><tr><td><h1>�����ύ����ʧ�ܣ���</h1><br>&nbsp;&nbsp;&nbsp;&nbsp;���Ѿ����û�кͷ�����ͨ���ˣ���ˣ���������Ϊ���Ѿ��뿪�ˣ������Զ�ע�������ĵ�¼״̬�����������ύ�����ϣ������һ����������ڣ����µ�¼��Ȼ�����ϸ��ƹ�ȥ�����ύ���������ô�������ύ����Щ���Ͻ��ᶪʧ��</td></tr></table>";
		$tmp .= "<table width='80%' align='center' border='1' bordercolor='#C0C0C0' cellpadding='3' style='border-collapse:collapse'>";
		foreach ($_POST as $key => $value) {
			$tmp .= "<tr><td width='100' align='right' bgcolor='#F0F0F0'>$key:</td><td>$value</td></tr>";
		}
		$tmp .= "</table>";
		echo $tmp;
		exit;
	}
	$to = base64_encode($_SERVER["PHP_SELF"]);
	//header("location:/v6/login.php");
	echo '<script> top.location = "/v6/login.php?' . mt_rand() . '"; </script>';
	exit;
}

// ~~~~~~~~~~ ��ȡ�û���Ϣ(uinfo = user information):
$ch_config = $config = array();
if (!$debug_mode) {
	if ($uinfo = $db->query("select * from sys_admin where id=$uid limit 1", 1)) {
		$realname = $uinfo["realname"];
		$shortcut = $uinfo["shortcut"];

		$chinfo = $db->query("select * from sys_character where id='" . $uinfo["character_id"] . "' limit 1", 1);
		$usermenu = $chinfo["menu"];
		$ch_config = $config = @unserialize($chinfo["config"]);

		$uid = $uinfo["id"];
	} else {
		exit("�û����ϲ����ڣ������µ�¼��");
	}
} else {
	$realname = $username;
	$usermenu = get_debug_menu();
}

if ($uinfo["module_config"] != "") {
	$_module_arr = wee_string_to_array($uinfo["module_config"]);
	foreach ($_module_arr as $k => $v) {
		$uinfo[$k] = $v;
	}
}
if ($debug_mode) {
	foreach ($special_config as $k => $v) {
		$uinfo[$k] = 1;
	}
}

$config["is_output"] = $uinfo["is_output"];

if ($debug_mode) {
	$config["guahao_config"] = array_keys($guahao_config_arr);
	$config["data_power"] = array_keys($data_power_arr);
	$config["show_wuxian"] = 1;
	$config["show_come_tel"] = 1;
	$config["show_tel"] = $uinfo["show_tel"] = 1;
} else {
	$config["guahao_config"] = explode(",", $uinfo["guahao_config"]);
	$config["data_power"] = explode(",", $uinfo["data_power"]);
	$config["show_tel"] = $uinfo["show_tel"];
}
$user_data_power = $config["data_power"];


// �Һź�������:
if ($debug_mode) {
	$gGuaHaoConfig = array_keys($guahao_config_arr);
} else {
	$gGuaHaoConfig = $config["guahao_config"];
	// ����Ƿ���ʧЧ��:
	foreach ($gGuaHaoConfig as $k => $v) {
		if (!array_key_exists($v, $guahao_config_arr)) {
			unset($gGuaHaoConfig[$k]);
		}
	}
}


// ~~~~~~~~~~ ��ȡ��ҳ����Ϣ(pinfo = page information):
// �޸ģ���ʹ�ô������ĵ��ý��в�ѯ����ѯ������ʹ���޲�����ҳ���ѯ:
$urlname = basename($_SERVER["REQUEST_URI"]);
$pagename = basename($_SERVER["PHP_SELF"]);
for ($w = 0; $w < 2; $w++) {
	if ($w == 0) {
		$findname = $urlname; //��һ��ʹ�ÿ����в�����ҳ������ѯ
	}
	if ($w == 1) {
		if ($urlname == $pagename) break; // ����в��������ֺ��޲���������һ�£���˵��ҳ��û�д��в������ã�Ӧ�����˷���ѯ
		$findname = $pagename; //�ڶ���ʹ��û�в�����ҳ������ѯ
	}
	/* echo $findname;
	exit; */
	if ($pinfo = $db->query("select * from sys_menu where link='$findname' or insertpage='$findname' or viewpage='$findname' or editpage='$findname' limit 1", 1)) {
		$pagesize = $pinfo["pagesize"];
		$htm["title"] = $pinfo["title"];
		$menuid = $pinfo["id"];
		/* echo $menuid;
		exit; */
		// ���㵱ǰҳ���Ȩ��:
		$pagepower = "";
		$mmenu = explode(";", $usermenu);
		/* print_r($mmenu);
		exit; */
		foreach ($mmenu as $mmenuitem) {
			list($mmainid, $mitemsdef) = explode(":", $mmenuitem);
			$mitems = explode(",", $mitemsdef);
			foreach ($mitems as $mitem) {
				list($itemid, $itempower) = explode("!", $mitem);
				if ($itemid == $menuid) {
					$pagepower = $itempower;
					break;
				}
			}
			if ($pagepower) break;
		}
		break;
	}
}
$pagesize = intval($pagesize) != 0 ? intval($pagesize) : $cfgDefaultPageSize;


// ����Ϣ���ڸ��µ����˵�ϵͳ��չ��(js)
$_mpage = $db->query("select * from sys_menu where link='$pagename' limit 1", 1);
$sys_menu_id = @intval($_mpage["id"]);
if ($_mpage["mid"] > 0) {
	$sys_menu_top_mid = $db->query("select id from sys_menu where mid=" . $_mpage["mid"] . " and type=1 limit 1", 1, "id");
}
if ($sys_menu_id > 0 && $sys_menu_top_mid > 0) {
	setcookie("sys_menu_id", $sys_menu_id, time() + 999999, "/");
	setcookie("sys_menu_top_mid", $sys_menu_top_mid, time() + 999999, "/");
}


// 2009-05-19 11:35
if ($debug_mode) {
	$hospital_ids = $db->query("select id from hospital where ishide=0", '', 'id');
} else {
	if ($uinfo["hospitals"] != '') {
		$hospital_ids = $db->query("select id from hospital where ishide=0 and id in (" . $uinfo["hospitals"] . ")", '', 'id');
		$uinfo["hospitals"] = implode(",", $hospital_ids);
		if (count($hospital_ids) == 1) {
			$_SESSION[$cfgSessionName]["hospital_id"] = intval($hospital_ids[0]);
		}
	} else {
		$hospital_ids = array();
	}
}

$hid = $user_hospital_id = intval($_SESSION[$cfgSessionName]["hospital_id"]);
$hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);


// ɾ��session���� @ 2016-10-13
$gc_time_log = ROOT . "./v6/data/ses_gc_time.txt";
$gc_last_time = @file_get_contents($gc_time_log);
if (time() - $gc_last_time >= 30) {
	@file_put_contents($gc_time_log, time()); //��������gc��ʱ�� ��ֹ������ͬʱgc
	ses_gc_by_set_env(); //��������
}

// ��ʼ�����ò���
if (isset($_REQUEST["id"])) $id = intval($_REQUEST["id"]);
if (isset($_REQUEST["op"])) $op = $_REQUEST["op"];