<?php
/*
// ����: ���� (weelia@126.com)
*/

$ku_config = @unserialize($uinfo["ku_config"]);

if ($uinfo["show_ku_tel"] > 0) {
	$ku_config["show_ku_tel"] = 1;
}
if ($uinfo["show_ku_report"] > 0) {
	$ku_config["show_ku_report"] = 1;
}
if ($uinfo["ku_data_limit"] != 0) {
	$ku_config["data_limit"] = $uinfo["ku_data_limit"];
}

if ($debug_mode) {
	$config["show_ziliaoku"] = 1;
	$ku_config["show_ku_tel"] = 1;
	$ku_config["data_limit"] = -9;
	$config["show_all_remind"] = 1;
}


// �������ܿ�����Ա
$is_daochu = in_array($realname, explode(" ", $sys_super_admin));

//��������Ȩ��(��ɾ��)
$is_super_admin = in_array($realname, explode(" ", $sys_super_admin));

// �Ƿ�΢�����ж�
$is_weixin = in_array($uinfo["character_id"], array(51, 50, 52)); //�Ƿ�΢������Ա
$is_weixin_zuzhang = in_array($uinfo["character_id"], array(51)); // �Ƿ�΢���鳤


function _ku_show_tel($line) {
	global $ku_config, $uid;
	if ($line["uid"] == $uid || $ku_config["show_ku_tel"] > 0) {
		return $line["mobile"];
	} else {
		if (strlen($line["mobile"]) == 11) {
			return substr($line["mobile"], 0, 3)."****".substr($line["mobile"], 7, 4);
		} else {
			if (strlen($line["mobile"]) < 7) {
				return $line["mobile"];
			}
			return substr($line["mobile"], 0, -4)."****";
		}
	}
}


?>