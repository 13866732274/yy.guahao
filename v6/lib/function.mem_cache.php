<?php
/*
// ˵��: ���úͶ�ȡmemcache
// ����: ���� (weelia@126.com)
// ʱ��: 2012-11-13
// ע��: ������ֻ����ϵͳMemcache��չ���ܣ���������������
*/

define("gUseMemCache", 1); //�������Ϊ0�������memcache�������
define("gMemPreFix", "brdd_"); //ȫ��ǰ׺�����ڽ��ͬһ���������ϵͳʹ��

function wee_mem_init() {
	if (!gUseMemCache) {
		return false;
	}

	if ($GLOBALS["mem"]) {
		return $GLOBALS["mem"];
	}

	ob_start();
	if (!class_exists("Memcache", false)) {
		echo "Memcache��δ��װ...<br>";
	} else {
		$GLOBALS["mem"] = $mem = new Memcache();
		$mem->connect("127.0.0.1", 8989);
	}
	$error = ob_get_clean();
	if ($error) {
		//echo "��ʼ��Memcache����: ".trim(strip_tags($error))."<br>";
		return false;
	}
	return $mem;
}

// �����ݴ洢��memcache:
// �洢�������У�
// type => array|string  ���ڲ�ʹ��
// save_time => ֵ�Ĵ洢ʱ��
// data => ���
function wee_mem_set_cache($name, $value, $timeout=600) {
	$mem = wee_mem_init();
	if ($mem === false) return false;

	$to_save = array();
	$to_save["type"] = is_array($value) ? "array" : "string";
	$to_save["save_time"] = time();

	if (is_array($value)) {
		$save_value = serialize($value);
	} else {
		$save_value = $value;
	}
	$to_save["data"] = $save_value;

	return $mem->set(gMemPreFix.$name, $to_save, 0, $timeout);
}


// ��memcache��ȡ���ݣ�ע�ⷵ��ֵ�ĸ�ʽ:
function wee_mem_get_cache($name) {
	$mem = wee_mem_init();
	if ($mem === false) return false;

	$arr = $mem->get(gMemPreFix.$name);
	if ($arr === false || !is_array($arr)) {
		return false;
	}
	if ($arr["type"] == "array") {
		$arr["data"] = @unserialize($arr["data"]);
	}

	unset($arr["type"]); //type������
	return $arr;
}

/*
// ����
include "v4/lib/set_env.php";
$uid_name_timeout = 8;
$tmp = @wee_mem_get_cache("uid_name");
if (is_array($tmp) && time() - $tmp["save_time"] <= $uid_name_timeout) {
	echo "����<br>";
	$uid_name = $tmp["data"];
} else {
	$uid_name = $db->query("select id,name from sys_admin", "id", "name");
	wee_mem_set_cache("uid_name", $uid_name, $uid_name_timeout);
}

echo "<pre>";
print_r($uid_name);
*/

?>